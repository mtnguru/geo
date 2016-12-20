<?php

namespace Drupal\multiversion\Entity\Storage\Sql;

use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageTrait;
use Drupal\node\NodeStorage as CoreNodeStorage;

/**
 * Storage handler for nodes.
 */
class NodeStorage extends CoreNodeStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait {
    delete as deleteEntities;
  }

  /**
   * {@inheritdoc}
   *
   * @todo: {@link https://www.drupal.org/node/2597534 Figure out why we need
   * this}, core seems to solve it some other way.
   */
  public function delete(array $entities) {
    // Delete all comments before deleting the nodes.
    try {
      $comment_storage = \Drupal::entityManager()->getStorage('comment');
      foreach ($entities as $entity) {
        if ($entity->comment) {
          $comments = $comment_storage->loadThread($entity, 'comment', 1);
          $comment_storage->delete($comments);
        }
      }
    }
    catch (\Exception $e) {
      // Failing likely due to comment module not being enabled. But we also
      // don't want node delete to fail because of broken comments.
    }
    $this->deleteEntities($entities);
  }

}
