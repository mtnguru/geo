<?php

/**
 * @file
 * Hooks specific to the Panels IPE module.
 */

use \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Act on a Panels Display before it is saved via the IPE.
 *
 * @param \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $panels_display
 *   The current Panels display.
 * @param array $layout_model
 *   The decoded LayoutModel from our App.
 */
function hook_panels_ipe_panels_display_presave(PanelsDisplayVariant $panels_display, array $layout_model) {
  if (isset($layout_model['use_custom_storage'])) {
    $configuration = $panels_display->getConfiguration();
    $panels_display->setStorage('custom_storage_key', $configuration['storage_id']);
  }
}

/**
 * Change the tempstore ID used by Panels IPE.
 *
 * Changes made to a Panels display using the IPE are stored in a shared
 * temporary storage object (tempstore) until the display either saved or
 * reverted to its previous state. This hook allows one to specify a different
 * key for storing changes in the tempstore.
 *
 * @param string $id
 *   The tempstore ID, passed by reference.
 * @param \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $panels_display
 *   The Panels display for which the tempstore ID will be used.
 */
function hook_panels_ipe_tempstore_id_alter(&$id, PanelsDisplayVariant $panels_display) {
  if ($panels_display->getBuilder()->getPluginId() == 'panelizer') {
    $id = 'panelizer';
  }
}
