<?php

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\file\FileStorageSchema as CoreFileStorageSchema;

/**
 * Storage schema handler for files.
 */
class FileStorageSchema extends CoreFileStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // @todo: Optimize indexes with the workspace field.
    return $schema;
  }

}
