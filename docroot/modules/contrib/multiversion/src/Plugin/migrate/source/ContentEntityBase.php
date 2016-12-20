<?php

namespace Drupal\multiversion\Plugin\migrate\source;

/**
 * Migration source class for content entities.
 *
 * @MigrateSource(
 *   id = "multiversion"
 * )
 */
class ContentEntityBase extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $storage = $this->entityManager->getStorage($this->entityTypeId);
    $entities = $storage->loadMultiple();

    $result = [];
    foreach ($entities as $entity_id => $entity) {
      foreach ($entity as $field_name => $field) {
        /** @var \Drupal\Core\Field\FieldItemListInterface $field */
        $value = $field->getValue();
        // If there is only one value in the field, unwrap it.
        if (count($value) == 1) {
          $value = reset($value);
          // If there's only one property in the field value, unwrap it.
          if (count($value) == 1) {
            $value = reset($value);
          }
        }
        // Set the 'migrate://' scheme for files.
        if ($this->entityTypeId == 'file' && $field_name == 'uri') {
          $target = file_uri_target($value);
          $value = 'migrate://' . $target;
        }
        $result[$entity_id][$field_name] = $value;
      }
    }

    // Make sure we don't migrate deleted entities.
    $storage_class = $storage->getEntityType()->getStorageClass();
    if (strpos($storage_class, 'Drupal\multiversion\Entity\Storage') !== FALSE) {
      foreach ($result as $entity_id => $entity) {
        if (isset($entity['_deleted']) && $entity['_deleted'] == 1) {
          unset($result[$entity_id]);
        }
      }
    }

    return new \ArrayIterator(array_values($result));
  }

}
