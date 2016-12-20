<?php

/**
 * @file
 * Contains \Drupal\module_filter\Form\ModuleFilterSettingsForm.
 */

namespace Drupal\module_filter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for module filter.
 */
class ModuleFilterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'module_filter_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['module_filter.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('module_filter.settings');

    $form['module_filter_set_focus'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Set focus to filter field on page load'),
      '#description' => $this->t('Currently has no effect when using Overlay module.'),
      '#default_value' => $config->get('module_filter_set_focus'),
    );

    $form['module_filter_tabs'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enhance the modules page with tabs'),
      '#description' => $this->t('Alternate tabbed theme that restructures packages into tabs.'),
      '#default_value' => $config->get('module_filter_tabs'),
    );

    $form['tabs'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Tabs'),
      '#description' => $this->t('Settings used with the tabs view of the modules page.'),
      '#open' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );

    $form['tabs']['module_filter_count_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Number of enabled modules'),
      '#description' => $this->t('Display the number of enabled modules in the active tab along with the total number of modules.'),
      '#default_value' => $config->get('module_filter_count_enabled'),
    );

    $form['tabs']['module_filter_visual_aid'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Visual aids'),
      '#description' => $this->t('When enabling/disabling modules, the module name will display in the tab summary.<br />When filtering, a count of results for each tab will be presented.'),
      '#default_value' => $config->get('module_filter_visual_aid'),
    );

    $form['tabs']['module_filter_hide_empty_tabs'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Hide tabs with no results'),
      '#description' => $this->t('When a filter returns no results for a tab, the tab is hidden. This is dependent on visual aids being enabled.'),
      '#default_value' => $config->get('module_filter_hide_empty_tabs'),
    );

    $form['tabs']['module_filter_dynamic_save_position'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Dynamically position Save button'),
      '#description' => $this->t("For sites with lots of tabs, enable to help keep the 'Save configuration' button more accessible."),
      '#default_value' => $config->get('module_filter_dynamic_save_position'),
    );

    $form['tabs']['module_filter_use_url_fragment'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use URL fragment'),
      '#description' => $this->t('Use URL fragment when navigating between tabs. This lets you use the browsers back/forward buttons to navigate through the tabs you selected.') . '<br />' . t('When the Overlay module is enabled this functionality will not be used since overlay relies on the URL fragment.'),
      '#default_value' => $config->get('module_filter_use_url_fragment'),
    );

    $form['tabs']['module_filter_use_switch'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use switch instead of checkbox'),
      '#description' => $this->t('This is purely cosmetic (at least for now). Displays a ON/OFF switch rather than a checkbox to enable/disable modules.<br /><strong>Modules will not actually be enabled/disabled until the form is saved.</strong>'),
      '#default_value' => $config->get('module_filter_use_switch'),
    );

    $form['tabs']['module_filter_track_recent_modules'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Track recently enabled/disabled modules'),
      '#description' => $this->t('Adds a "Recent" tab that displays modules that have been enabled or disabled with the last week.'),
      '#default_value' => $config->get('module_filter_track_recent_modules'),
    );

    $form['tabs']['module_filter_remember_active_tab'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Remember active tab.'),
      '#description' => $this->t('When enabled, the active tab will be remembered.'),
      '#default_value' => $config->get('module_filter_remember_active_tab'),
    );

    $form['update'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Update status'),
      '#collapsible' => TRUE,
      '#collapsed' => (\Drupal::moduleHandler()->moduleExists('update')) ? FALSE : TRUE,
    );

    $form['update']['module_filter_remember_update_state'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Remember the last selected filter.'),
      '#description' => $this->t('When enabled, the last state (All, Update available, Security update, Unknown) will be remembered.'),
      '#default_value' => $config->get('module_filter_remember_update_state'),
    );

    if (\Drupal::moduleHandler()->moduleExists('page_actions')) {
      $form['tabs']['module_filter_dynamic_save_position']['#description'] .= '<br />' . $this->t('The module %name is enabled and thus this setting will have no affect.', array('%name' => $this->t('Page actions')));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('module_filter.settings')
      ->set('module_filter_set_focus', $form_state->getValue('module_filter_set_focus'))
      ->set('module_filter_tabs', $form_state->getValue('module_filter_tabs'))
      ->set('module_filter_count_enabled', $form_state->getValue('module_filter_count_enabled'))
      ->set('module_filter_visual_aid', $form_state->getValue('module_filter_visual_aid'))
      ->set('module_filter_hide_empty_tabs', $form_state->getValue('module_filter_hide_empty_tabs'))
      ->set('module_filter_dynamic_save_position', $form_state->getValue('module_filter_dynamic_save_position'))
      ->set('module_filter_use_url_fragment', $form_state->getValue('module_filter_use_url_fragment'))
      ->set('module_filter_use_switch', $form_state->getValue('module_filter_use_switch'))
      ->set('module_filter_track_recent_modules', $form_state->getValue('module_filter_track_recent_modules'))
      ->set('module_filter_remember_active_tab', $form_state->getValue('module_filter_remember_active_tab'))
      ->set('module_filter_remember_update_state', $form_state->getValue('module_filter_remember_update_state'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
