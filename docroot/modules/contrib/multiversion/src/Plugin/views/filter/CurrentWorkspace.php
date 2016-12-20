<?php

namespace Drupal\multiversion\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter by current workspace.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("current_workspace")
 */
class CurrentWorkspace extends FilterPluginBase {

  public function adminSummary() { }

  protected function operatorForm(&$form, FormStateInterface $form_state) { }

  public function canExpose() { return FALSE; }

  public function query() {
    $table = $this->ensureMyTable();
    $active_workspace = \Drupal::service('workspace.manager')->getActiveWorkspace()->id();
    $placeholder = $this->placeholder();
    $this->query->addWhereExpression($this->options['group'], "$table.workspace = $placeholder", array($placeholder => $active_workspace));
  }

}
