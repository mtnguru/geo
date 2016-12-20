<?php

namespace Drupal\panels_ipe;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * A trait for getting the tempstore ID of a Panels IPE display.
 */
trait TempStoreTrait {

  /**
   * Returns the tempstore ID of a Panels IPE display.
   *
   * Modules will be allowed to alter the ID via
   * hook_panels_ipe_tempstore_id_alter().
   *
   * @param \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $panels_display
   *   The Panels display.
   *
   * @return string
   *   The tempstore ID to use. Defaults to the display ID.
   *
   * @throws \UnexpectedValueException
   *   If the tempstore ID is empty after being altered by modules.
   */
  protected function getTempStoreId(PanelsDisplayVariant $panels_display) {
    $id = $panels_display->id();

    // Get the module handler. We cannot define a $moduleHandler property in
    // this trait, or we could get fatal errors if classes that use this trait
    // have a $moduleHandler property of their own.
    if (isset($this->moduleHandler) && $this->moduleHandler instanceof ModuleHandlerInterface) {
      $module_handler = $this->moduleHandler;
    }
    else {
      $module_handler = \Drupal::moduleHandler();
    }

    // Allow modules to alter the tempstore ID.
    $module_handler
      ->alter('panels_ipe_tempstore_id', $id, $panels_display);

    if (empty($id)) {
      throw new \UnexpectedValueException('Tempstore ID cannot be empty');
    }
    return $id;
  }

}
