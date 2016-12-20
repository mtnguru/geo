<?php

namespace Drupal\workspace;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\multiversion\Workspace\ConflictTrackerInterface;
use Drupal\replication\Entity\ReplicationLog;
use Drupal\replication\ReplicationTask\ReplicationTask;
use Drupal\replication\ReplicationTask\ReplicationTaskInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides the Replicator manager.
 */
class ReplicatorManager implements ReplicatorInterface {

  /**
   * The services available to perform replication.
   *
   * @var ReplicatorInterface[]
   */
  protected $replicators = [];

  /**
   * The injected service to track conflicts during replication.
   *
   * @var ConflictTrackerInterface
   */
  protected $conflictTracker;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The injected service to track conflicts during replication.
   *
   * @param ConflictTrackerInterface $conflict_tracker
   *   The confict tracking service.
   * @param EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(ConflictTrackerInterface $conflict_tracker, EventDispatcherInterface $event_dispatcher) {
    $this->conflictTracker = $conflict_tracker;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(WorkspacePointerInterface $source, WorkspacePointerInterface $target) {
    return TRUE;
  }

  /**
   * Adds replication services.
   *
   * @param ReplicatorInterface $replicator
   *   The service to make available for performing replication.
   */
  public function addReplicator(ReplicatorInterface $replicator) {
    $this->replicators[] = $replicator;
  }

  /**
   * {@inheritdoc}
   */
  public function replicate(WorkspacePointerInterface $source, WorkspacePointerInterface $target, $task = NULL) {
    // It is assumed a caller of replicate will set this static variable to
    // FALSE if they wish to proceed with replicating content upstream even in
    // the presence of conflicts. If the caller wants to make sure no conflicts
    // are replicated to the upstream, set this value to TRUE.
    // By default, the value is FALSE so as not to break the previous
    // behavior.
    // @todo Use a sequence index instead of boolean? This will allow the
    // caller to know there haven't been additional conflicts.
    $is_aborted_on_conflict = drupal_static('workspace_is_aborted_on_conflict', FALSE);

    // Abort updating the Workspace if there are conflicts.
    $initial_conflicts = $this->conflictTracker->useWorkspace($source->getWorkspace())->getAll();
    if ($is_aborted_on_conflict && $initial_conflicts) {
      return $this->failedReplicationLog($source, $target, $task);
    }

    // Derive a pull replication task from the Workspace we are acting on.
    $pull_task = $this->getTask($source->getWorkspace(), 'pull_replication_settings');

    // Pull in changes from $target to $source to ensure a merge will complete.
    $this->update($target, $source, $pull_task);

    // Abort replicating to target Workspace if there are conflicts.
    $post_conflicts = $this->conflictTracker->useWorkspace($source->getWorkspace())->getAll();
    if ($is_aborted_on_conflict && $post_conflicts) {
      return $this->failedReplicationLog($source, $target, $task);
    }

    // Automatically derive settings from the workspace if no task sent.
    // @todo Refactor to eliminate obscurity of having an optional parameter
    // and automatically setting the parameter's value.
    if ($task === NULL) {
      // Derive a push replication task from the Workspace we are acting on.
      $task = $this->getTask($source->getWorkspace(), 'push_replication_settings');
    }

    // Push changes from $source to $target.
    $push_log = $this->doReplication($source, $target, $task);

    return $push_log;
  }

  /**
   * Derives a replication task from an entity with replication settings.
   *
   * This can be used with a Workspace using the 'push_replication_settings'
   * and 'pull_replication_settings' fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to derive the replication task from.
   * @param string $field_name
   *   The field name that references a ReplicationSettings config entity.
   *
   * @return \Drupal\replication\ReplicationTask\ReplicationTaskInterface
   *   A replication task that can be passed to a replicator.
   *
   * @throws \Symfony\Component\Console\Exception\LogicException
   *   The replication settings field does not exist on the entity.
   */
  public function getTask(EntityInterface $entity, $field_name) {
    $task = new ReplicationTask();
    $items = $entity->get($field_name);

    if (!$items instanceof EntityReferenceFieldItemListInterface) {
      throw new LogicException('Replication settings field does not exist.');
    }

    $referenced_entities = $items->referencedEntities();
    if (count($referenced_entities) > 0) {
      $task->setFilter($referenced_entities[0]->getFilterId());
      $task->setParameters($referenced_entities[0]->getParameters());
    }

    return $task;
  }

  /**
   * Update the target using the source before doing a replication.
   *
   * This is used primarily as a public facing method by the UpdateForm. It
   * avoids the additional logic found in the replicate method.
   *
   * @param \Drupal\workspace\WorkspacePointerInterface $target
   *   The workspace to replicate to.
   * @param \Drupal\workspace\WorkspacePointerInterface $source
   *   The workspace to replicate from.
   * @param mixed $task
   *   Optional information that defines the replication task to perform.
   *
   * @return ReplicationLog
   *   The log entry for this replication.
   */
  public function update(WorkspacePointerInterface $target, WorkspacePointerInterface $source, $task = NULL) {
    return $this->doReplication($target, $source, $task);
  }

  /**
   * Internal method to contain replication logic.
   *
   * @param \Drupal\workspace\WorkspacePointerInterface $source
   *   The workspace to replicate from.
   * @param \Drupal\workspace\WorkspacePointerInterface $target
   *   The workspace to replicate to.
   * @param mixed $task
   *   Optional information that defines the replication task to perform.
   *
   * @return ReplicationLog
   *   The log entry for this replication.
   */
  protected function doReplication(WorkspacePointerInterface $source, WorkspacePointerInterface $target, $task = NULL) {
    foreach ($this->replicators as $replicator) {
      if ($replicator->applies($source, $target)) {
        // @TODO: Get rid of this meta-programming once #2814055 lands in
        // Replication.
        $events_class = '\Drupal\replication\Event\ReplicationEvents';
        $event_class = '\Drupal\replication\Event\ReplicationEvent';

        if (class_exists($events_class) && class_exists($event_class)) {
          $event = new $event_class($source->getWorkspace(), $target->getWorkspace());
        }

        // Dispatch the pre-replication event, if the event object exists.
        if (isset($event)) {
          $this->eventDispatcher->dispatch($events_class::PRE_REPLICATION, $event);
        }

        // Do the mysterious dance of replication...
        $log = $replicator->replicate($source, $target, $task);

        // ...and dispatch the post-replication event, if the event object
        // exists.
        if (isset($event)) {
          $this->eventDispatcher->dispatch($events_class::POST_REPLICATION, $event);
        }

        return $log;
      }
    }

    return $this->failedReplicationLog($source, $target);
  }

  /**
   * Generate a failed replication log and return it.
   *
   * @param \Drupal\workspace\WorkspacePointerInterface $source
   *   The workspace to replicate from.
   * @param \Drupal\workspace\WorkspacePointerInterface $target
   *   The workspace to replicate to.
   *
   * @return ReplicationLog
   *   The log entry for this replication.
   */
  protected function failedReplicationLog(WorkspacePointerInterface $source, WorkspacePointerInterface $target) {
    $time = new \DateTime();
    $history = [
      'start_time' => $time->format('D, d M Y H:i:s e'),
      'end_time' => $time->format('D, d M Y H:i:s e'),
      'session_id' => \md5((\microtime(TRUE) * 1000000)),
    ];
    $replication_log_id = $source->generateReplicationId($target);
    /** @var \Drupal\replication\Entity\ReplicationLogInterface $replication_log */
    $replication_log = ReplicationLog::loadOrCreate($replication_log_id);
    $replication_log->set('ok', FALSE);
    $replication_log->setSessionId($history['session_id']);
    $replication_log->setHistory($history);
    $replication_log->save();
    return $replication_log;
  }

}
