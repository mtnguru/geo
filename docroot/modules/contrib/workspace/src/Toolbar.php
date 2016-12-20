<?php


namespace Drupal\workspace;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Drupal\workspace\Form\WorkspaceSwitcherForm;

/**
 * Service for hooks and utilities related to Toolbar integration.
 */
class Toolbar {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new Toolbar.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   *   The workspace manager service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param AccountInterface $current_user
   *   The current user service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, WorkspaceManagerInterface $workspace_manager, FormBuilderInterface $form_builder, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->workspaceManager = $workspace_manager;
    $this->formBuilder = $form_builder;
    $this->currentUser = $current_user;
  }

  /**
   * Hook bridge;  Responds to hook_toolbar().
   *
   * @see hook_toolbar().
   */
  public function toolbar() {
    $items = [];

    $active = $this->workspaceManager->getActiveWorkspace();

    $items['workspace_switcher'] = [
      // Include the toolbar_tab_wrapper to style the link like a toolbar tab.
      // Exclude the theme wrapper if custom styling is desired.
      '#type' => 'toolbar_item',
      '#weight' => 125,
      '#wrapper_attributes' => [
        'class' => ['workspace-toolbar-tab'],
      ],
      '#attached' => [
        'library' => [
          'workspace/drupal.workspace.toolbar',
        ],
      ],
    ];

    $items['workspace_switcher']['tab'] = [
      '#type' => 'link',
      '#title' => $this->t('@active', ['@active' => $active->label()]),
      '#url' => Url::fromRoute('entity.workspace.collection'),
      '#attributes' => [
        'title' => $this->t('Switch workspaces'),
        'class' => ['toolbar-icon', 'toolbar-icon-workspace'],
      ],
    ];

    $create_link = [
      '#type' => 'link',
      '#title' => t('Add workspace'),
      '#url' => Url::fromRoute('entity.workspace.add'),
      '#options' => array('attributes' => array('class' => array('add-workspace'))),
    ];

    $items['workspace_switcher']['tray'] = [
      '#heading' => $this->t('Switch to workspace'),
      '#pre_render' => ['workspace.toolbar:preRenderWorkspaceSwitcherForms'],
      // This wil get filled in via pre-render.
      'workspace_forms' => [],
      'create_link' => $create_link,
      '#cache' => [
        'contexts' => $this->entityTypeManager->getDefinition('workspace')->getListCacheContexts(),
        'tags' => $this->entityTypeManager->getDefinition('workspace')->getListCacheTags(),
      ],
      '#attributes' => [
        'class' => ['toolbar-menu'],
      ],
    ];

    $user = \Drupal::currentUser();
    $update_access = $user->hasPermission('update any workspace from upstream');
    $has_upstream = isset($active->upstream) && !$active->upstream->isEmpty();
    if ($update_access && $has_upstream) {
      $items['workspace_update'] = [
        '#type' => 'toolbar_item',
        '#weight' => 124,
        'tab' => [
          '#type' => 'link',
          '#title' => t('Update'),
          '#url' => Url::fromRoute('workspace.update.form'),
          '#attributes' => [
            'title' => t('Update current workspace from upstream'),
            'class' => [
              'toolbar-icon',
              'toolbar-icon-workspace-update',
              'use-ajax'
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode([
              'width' => '50%',
            ])
          ],
        ],
        '#wrapper_attributes' => [
          'class' => ['workspace-update-toolbar-tab'],
        ],
        '#attached' => [
          'library' => [
            'workspace/drupal.workspace.toolbar',
          ],
        ],
      ];
    }

    return $items;
  }

  /**
   * Prerender callback; Adds the workspace switcher forms to the render array.
   *
   * @param array $element
   *
   * @return array
   *   The modified $element.
   */
  public function preRenderWorkspaceSwitcherForms(array $element) {
    foreach ($this->allWorkspaces() as $workspace) {
      $element['workspace_forms']['workspace_' . $workspace->getMachineName()] = $this->formBuilder->getForm(WorkspaceSwitcherForm::class, $workspace);
    }

    return $element;
  }

  /**
   * Returns a list of all defined and accessible workspaces.
   *
   * Note: This assumes that the total number of workspaces on the site is
   * very small.  If it's actually large this method will have memory issues.
   *
   * @return WorkspaceInterface[]
   */
  protected function allWorkspaces() {
    return array_filter($this->entityTypeManager->getStorage('workspace')->loadMultiple(), function(WorkspaceInterface $workspace) {
      return $workspace->access('view', $this->currentUser);
    });
  }
}
