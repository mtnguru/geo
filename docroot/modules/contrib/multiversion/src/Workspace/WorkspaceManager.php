<?php

namespace Drupal\multiversion\Workspace;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;

class WorkspaceManager implements WorkspaceManagerInterface {
  use StringTranslationTrait;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var array
   */
  protected $negotiators = array();

  /**
   * @var array
   */
  protected $sortedNegotiators;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(RequestStack $request_stack, EntityManagerInterface $entity_manager, AccountProxyInterface $current_user, LoggerInterface $logger = NULL) {
    $this->requestStack = $request_stack;
    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
    $this->logger = $logger ?: new NullLogger();
  }

  /**
   * {@inheritdoc}
   */
  public function addNegotiator(WorkspaceNegotiatorInterface $negotiator, $priority) {
    $this->negotiators[$priority][] = $negotiator;
    $this->sortedNegotiators = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function load($workspace_id) {
    return $this->entityManager->getStorage('workspace')->load($workspace_id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $workspace_ids = NULL) {
    return $this->entityManager->getStorage('workspace')->loadMultiple($workspace_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadByMachineName($machine_name) {
    $workspaces = $this->entityManager->getStorage('workspace')->loadByProperties(['machine_name' => $machine_name]);
    return current($workspaces);
  }

  /**
   * {@inheritdoc}
   *
   * @todo {@link https://www.drupal.org/node/2600382 Access check.}
   */
  public function getActiveWorkspace() {
    $request = $this->requestStack->getCurrentRequest();
    foreach ($this->getSortedNegotiators() as $negotiator) {
      if ($negotiator->applies($request)) {
        if ($workspace_id = $negotiator->getWorkspaceId($request)) {
          if ($workspace = $this->load($workspace_id)) {
            return $workspace;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveWorkspace(WorkspaceInterface $workspace) {
    $default_workspace_id = \Drupal::getContainer()->getParameter('workspace.default');
    // If the current user doesn't have access to view the workspace, they
    // shouldn't be allowed to switch to it.
    // @todo Could this be handled better?
    if (!$workspace->access('view') && ($workspace->id() != $default_workspace_id)) {
      $this->logger->error('Denied access to view workspace {workspace}', ['workspace' => $workspace->label()]);
      throw new WorkspaceAccessException('The user does not have permission to view that workspace.');
    }

    // Set the workspace on the proper negotiator.
    $request = $this->requestStack->getCurrentRequest();
    foreach ($this->getSortedNegotiators() as $negotiator) {
      if ($negotiator->applies($request)) {
        $negotiator->persist($workspace);
        break;
      }
    }

    return $this;
  }

  /**
   * @return \Drupal\multiversion\Workspace\WorkspaceNegotiatorInterface[]
   */
  protected function getSortedNegotiators() {
    if (!isset($this->sortedNegotiators)) {
      // Sort the negotiators according to priority.
      krsort($this->negotiators);
      // Merge nested negotiators from $this->negotiators into
      // $this->sortedNegotiators.
      $this->sortedNegotiators = array();
      foreach ($this->negotiators as $builders) {
        $this->sortedNegotiators = array_merge($this->sortedNegotiators, $builders);
      }
    }
    return $this->sortedNegotiators;
  }

}
