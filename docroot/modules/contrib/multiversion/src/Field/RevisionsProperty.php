<?php

namespace Drupal\multiversion\Field;

use Drupal\Core\TypedData\TypedData;

/**
 * The 'revisions' property for revision token fields.
 */
class RevisionsProperty extends TypedData {

  /**
   * @var array
   */
  protected $value = [];

  /**
   * {@inheritdoc}
   */
  public function getValue($langcode = NULL) {
    if (!empty($this->value)) {
      return $this->value;
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getRoot()->getValue();

    $workspace = isset($entity->workspace) ? $entity->workspace->entity : null;
    $branch = \Drupal::service('multiversion.entity_index.factory')
      ->get('multiversion.entity_index.rev.tree', $workspace)
      ->getDefaultBranch($entity->uuid());

    if (empty($branch) && !$entity->_rev->is_stub && !$entity->isNew()) {
      list($i, $hash) = explode('-', $entity->_rev->value);
      $this->value = [$hash];
    }
    else {
      // We want children first and parent last.
      foreach (array_reverse($branch) as $rev => $status) {
        list($i, $hash) = explode('-', $rev);
        $this->value[] = $hash;
      }
    }

    return $this->value;
  }

}
