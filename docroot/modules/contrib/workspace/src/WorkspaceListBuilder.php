<?php

namespace Drupal\workspace;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of workspace entities.
 *
 * @see \Drupal\multiversion\Entity\Workspace
 */
class WorkspaceListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('workspace.manager')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   *   The workspace manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, WorkspaceManagerInterface $workspace_manager) {
    parent::__construct($entity_type, $storage);
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // @todo should we show conflicted state in this list?
    $header['label'] = t('Workspace');
    $header['uid'] = t('Owner');
    $header['type'] = t('Type');
    $header['status'] = t('Status');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\multiversion\Entity\WorkspaceInterface $entity */
    $row['label'] = $entity->label() . ' (' . $entity->getMachineName() . ')';
    $row['owner'] = $entity->getOwner()->getDisplayname();
    /** @var \Drupal\multiversion\Entity\WorkspaceTypeInterface $type */
    $type = $entity->get('type')->first()->entity;
    $row['type'] = $type ? $type->label() : '';
    $active_workspace = $this->workspaceManager->getActiveWorkspace()->id();
    $row['status'] = $active_workspace == $entity->id() ? 'Active' : 'Inactive';
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\multiversion\Entity\WorkspaceInterface $entity */
    $operations = parent::getDefaultOperations($entity);
    if (isset($operations['edit'])) {
      $operations['edit']['query']['destination'] = $entity->toUrl('collection');
    }

    $active_workspace = $this->workspaceManager->getActiveWorkspace()->id();
    if ($entity->id() != $active_workspace) {
      $operations['activate'] = array(
        'title' => $this->t('Set Active'),
        'weight' => 20,
        'url' => $entity->toUrl('activate-form', ['query' => ['destination' => $entity->toUrl('collection')]]),
      );
    }

    $operations['conflicts'] = [
      'title' => $this->t('View Conflicts'),
      'weight' => 21,
      'url' => $entity->toUrl('conflicts', ['workspace' => $entity->id()]),
    ];

    return $operations;
  }

}
