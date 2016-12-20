<?php

namespace Drupal\multiversion\Entity\Query;

/**
 * @property $entityTypeId
 * @property $entityManager
 * @property $condition
 */
trait QueryTrait {

  /**
   * @var null|string
   */
  protected $workspaceId = NULL;

  /**
   * @var boolean
   */
  protected $isDeleted = FALSE;

  /**
   *
   */
  public function useWorkspace($id) {
    $this->workspaceId = $id;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isDeleted()
   */
  public function isDeleted() {
    $this->isDeleted = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotDeleted()
   */
  public function isNotDeleted() {
    $this->isDeleted = FALSE;
    return $this;
  }

  public function prepare() {
    parent::prepare();
    $entity_type = $this->entityManager->getDefinition($this->entityTypeId);
    $storage_class = $entity_type->getStorageClass();
    // Add necessary conditions just when the storage class is defined by the
    // Multiversion module. This is needed when uninstalling Multiversion.
    if (strpos($storage_class, 'Drupal\multiversion\Entity\Storage') !== FALSE) {
      $revision_key = $entity_type->getKey('revision');
      $revision_query = FALSE;
      foreach ($this->condition->conditions() as $condition) {
        if ($condition['field'] == $revision_key) {
          $revision_query = TRUE;
        }
      }

      // Loading a revision is explicit. So when we try to load one we should do
      // so without a condition on the deleted flag.
      if (!$revision_query) {
        $this->condition('_deleted', (int) $this->isDeleted);
      }
    }
    return $this;
  }

}
