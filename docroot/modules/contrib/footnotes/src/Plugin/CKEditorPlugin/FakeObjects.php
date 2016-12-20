<?php

/**
 * @file
 * Contains \Drupal\footnotes\Plugin\CKEditorPlugin\FakeObjects.
 */

namespace Drupal\footnotes\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\ckeditor\CKEditorPluginButtonsInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "FakeObjects" plugin.
 *
 * This is a dependency to footnotes, the source comes from
 * http://ckeditor.com/addon/fakeobjects.
 *
 * @CKEditorPlugin(
 *   id = "fakeobjects",
 *   label = @Translation("FakeObjects")
 * )
 */
class FakeObjects extends PluginBase implements CKEditorPluginInterface, CKEditorPluginButtonsInterface {

  /**
   * Implements CKEditorPluginInterface::getDependencies().
   */
  public function getDependencies(Editor $editor) {
    return array();
  }

  /**
   * Implements CKEditorPluginInterface::getLibraries().
   */
  public function getLibraries(Editor $editor) {
    return array();
  }

  /**
   * Implements CKEditorPluginInterface::isInternal().
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * Implements CKEditorPluginInterface::getFile().
   */
  public function getFile() {
    return drupal_get_path('module', 'footnotes') . '/assets/js/ckeditor/fakeobjects/plugin.js';
  }

  /**
   * Implements CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return array();
  }

  /**
   * Implements \CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    return array();
  }

}
