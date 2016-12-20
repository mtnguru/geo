<?php

/**
 * @file
 * Contains \Drupal\module_filter\Controller\ModuleFilterUpdateController.
 */

namespace Drupal\module_filter\Controller;

use Drupal\update\Controller\UpdateController;

/**
 * Wrapper function for update_status().
 */
class ModuleFilterUpdateController extends UpdateController {
  public function updateStatus() {
    $this->moduleHandler()->loadInclude('update', 'report.inc');
    $update_report = parent::updateStatus();
    return [
      'module_filter' => $this->formBuilder()->getForm('Drupal\module_filter\Form\ModuleFilterUpdateStatusForm'),
      'update_report' => $update_report,
    ];
  }

}
