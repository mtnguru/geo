<?php

namespace Drupal\multiversion\Plugin\migrate\process;

use Drupal\Component\Utility\Html;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "text_field_process"
 * )
 */
class TextFieldProcess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Escapes text by converting special characters to HTML entities.
    return Html::escape($value);
  }

}
