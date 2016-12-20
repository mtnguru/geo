<?php

namespace Drupal\workspace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\multiversion\Entity\Workspace;
use Drupal\multiversion\Entity\WorkspaceType;
use Drupal\multiversion\Entity\WorkspaceTypeInterface;
use Drupal\workspace\Controller\Component\ConflictListBuilder;

class WorkspaceController extends ControllerBase {

  public function add() {
    $types = WorkspaceType::loadMultiple();
    if ($types && count($types) == 1) {
      $type = reset($types);
      return $this->addForm($type);
    }
    if (count($types) === 0) {
      return array(
        '#markup' => $this->t('You have not created any Workspace types yet. Go to the <a href=":url">Workspace type creation page</a> to add a new Workspace type.', [
          ':url' => Url::fromRoute('entity.workspace_type.add')->toString(),
        ]),
      );
    }

    return array('#theme' => 'workspace_add_list', '#content' => $types);

  }

  public function addForm(WorkspaceTypeInterface $workspace_type) {
    $workspace = Workspace::create([
      'type' => $workspace_type->id()
    ]);
    return $this->entityFormBuilder()->getForm($workspace);
  }

  public function getAddFormTitle(WorkspaceTypeInterface $workspace_type) {
    return $this->t('Add %type workspace', array('%type' => $workspace_type->label()));
  }

  /**
   * View a list of conflicts for a workspace.
   *
   * @param string $workspace
   *   The workspace ID to get conflicts for.
   *
   * @return array
   *   The render array to display for the page.
   */
  public function viewConflicts($workspace) {
    $container = \Drupal::getContainer();
    $builder = ConflictListBuilder::createInstance($container);
    return $builder->buildList($workspace);
  }

  /**
   * Get the page title for the list of conflicts page.
   *
   * @return string
   *   The page title.
   */
  public function getViewConflictsTitle() {
    return 'Workspace Conflicts';
  }

}
