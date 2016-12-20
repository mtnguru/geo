<?php

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\node\NodeStorageSchema as CoreNodeStorageSchema;

/**
 * Storage schema handler for nodes.
 */
class NodeStorageSchema extends CoreNodeStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // @todo: Optimize indexes with the workspace field.
    return $schema;
  }

}
