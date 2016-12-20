<?php

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\taxonomy\TermStorageSchema as CoreTermStorageSchema;

/**
 * Storage schema handler for taxonomy terms.
 */
class TermStorageSchema extends CoreTermStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // @todo: Optimize indexes with the workspace field.
    return $schema;
  }

}
