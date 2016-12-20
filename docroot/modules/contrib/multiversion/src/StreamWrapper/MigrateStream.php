<?php

namespace Drupal\multiversion\StreamWrapper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StreamWrapper\LocalStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;

/**
 * Defines a Drupal translations (migrate://) stream wrapper class.
 *
 * Provides support for storing files during migration.
 */
class MigrateStream extends LocalStream {

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::LOCAL_NORMAL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return t('Migration files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Migration files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return static::basePath();
  }

  /**
   * Implements Drupal\Core\StreamWrapper\StreamWrapperInterface::getExternalUrl().
   * @return string
   *   Returns a string containing a web accessible URL for the resource.
   */
  function getExternalUrl() {
    $path = str_replace('\\', '/', $this->getTarget());
    return $GLOBALS['base_url'] . '/' . static::basePath() . '/' . UrlHelper::encodePath($path);
  }

  /**
   * Returns the base path for migrate://.
   *
   * @return string
   *   The base path for migrate://.
   */
  public static function basePath() {
    return \Drupal::service('site.path') . '/files/migrate';
  }

}
