<?php

/**
 * @file
 * Contains \Drupal\module_filter\Element\ModuleFilter.
 */

namespace Drupal\module_filter\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a one-line text field form element.
 *
 * @FormElement("module_filter")
 */
class ModuleFilter extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#input' => TRUE,
      '#process' => array(
        array($class, 'processModuleFilter'),
        array($class, 'processAjaxForm'),
      ),
      '#weight' => -1,
      '#tree' => TRUE,
      '#theme' => 'module_filter',
      '#theme_wrappers' => array('form_element'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    /*if ($input !== FALSE && $input !== NULL) {
      // This should be a string, but allow other scalars since they might be
      // valid input in programmatic form submissions.
      if (!is_scalar($input)) {
        $input = '';
      }
      return str_replace(array("\r", "\n"), '', $input);
    }
    return NULL;*/
    //return $input;
    return NULL;
  }

  /**
   * #process callback for #modulefilter form element property.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic input element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processModuleFilter(&$element, FormStateInterface $form_state, &$complete_form) {
    $config = \Drupal::config('module_filter.settings');
    $module_handler = \Drupal::moduleHandler();
    $element['name'] = array(
      '#type' => 'textfield',
      '#title' => (isset($element['#title'])) ? $element['#title'] : t('Filter list'),
      '#default_value' => (isset($element['#default_value'])) ? $element['#default_value'] : ((isset($_GET['filter'])) ? $_GET['filter'] : ''),
      '#size' => (isset($element['#size'])) ? $element['#size'] : 45,
      '#weight' => (isset($element['#weight'])) ? $element['#weight'] : -10,
      '#attributes' => ((isset($element['#attributes'])) ? $element['#attributes'] : array()) + array('autocomplete' => 'off'),
      '#attached' => array(
        'library' => array(
          'module_filter/drupal.module_filter',
        ),
        'drupalSettings' => array(
          'moduleFilter' => array(
            'setFocus' => $config->get('module_filter_set_focus'),
            'tabs' => $config->get('module_filter_tabs'),
            'countEnabled' => $config->get('module_filter_count_enabled'),
            'visualAid' => $config->get('module_filter_visual_aid'),
            'hideEmptyTabs' => $config->get('module_filter_hide_empty_tabs'),
            'dynamicPosition' => !$module_handler->moduleExists('page_actions') ? $config->get('module_filter_dynamic_save_position') : FALSE,
            'useURLFragment' => $config->get('module_filter_use_url_fragment'),
            'useSwitch' => $config->get('module_filter_use_switch'),
            'trackRecent' => $config->get('module_filter_track_recent_modules'),
            'rememberActiveTab' => $config->get('module_filter_remember_active_tab'),
            'rememberUpdateState' => $config->get('module_filter_remember_update_state'),
          ),
        ),
      ),
    );
    if (isset($element['#description'])) {
      $element['name']['#description'] = $element['#description'];
    }
    return $element;
  }

  /**
   * Prepares a #type 'textfield' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  /*public static function preRenderTextfield($element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, array('id', 'name', 'value', 'size', 'maxlength', 'placeholder'));
    static::setAttributes($element, array('form-text'));

    return $element;
  }*/

}
