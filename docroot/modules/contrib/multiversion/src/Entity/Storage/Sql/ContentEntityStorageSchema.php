<?php

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema as CoreContentEntityStorageSchema;

/**
 * Storage schema handler for generic content entities.
 */
class ContentEntityStorageSchema extends CoreContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // @todo: Optimize indexes with the workspace field.
    return $schema;
  }

}
