<?php

/**
 * @file
 * Preprocessors and theme functions of Module filter module.
 */

use Drupal\Core\Render\Element;
use Drupal\views_ui\ViewFormBase;
use Drupal\Core\Url;

/**
 * Prepares variables for module filter templates.
 *
 * Default template: module-filter.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An array of elements to display in view mode.
 */
function template_preprocess_module_filter(&$variables) {
  //$element = $variables['element'];
}

/**
 * Prepares variables for module filter system modules tabs templates.
 *
 * Default template: module-filter-system-modules-tabs.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 */
function template_preprocess_module_filter_system_modules_tabs(&$variables) {
  $module_handler = \Drupal::moduleHandler();
  $config = \Drupal::service('config.factory')->getEditable('module_filter.settings');
  $renderer = \Drupal::service('renderer');
  $variables['modules'] = [];
  $form = $variables['form'];

  if ($module_handler->moduleExists('views_ui')) {
    // Hack to get consistent style with views ctools dropbutton.
    $form['module_filter']['#attached']['library'][] = 'views_ui/admin.styling';
  }

  if (!$module_handler->moduleExists('page_actions')) {
    $form['actions']['#prefix'] = '<div id="module-filter-submit">';
    $form['actions']['#suffix'] = '</div>';
  }

  $header = [
    ['data' => '', 'class' => ['checkbox']],
    ['data' => t('Name'), 'class' => ['name']],
    ['data' => t('Description'), 'class' => ['description']],
    ['data' => t('Links'), 'class' => ['links']],
  ];

  $package_ids = ['all'];
  $enabled['all'] = [];

  if ($config->get('module_filter_track_recent_modules')) {
    $saved_recent_modules = ($config->get('module_filter_recent_modules')) ? $config->get('module_filter_recent_modules') : [];
    $recent_modules = array_filter($saved_recent_modules, '_module_filter_recent_filter');

    // Save the filtered results.
    $config->set('module_filter_recent_modules', $recent_modules)->save();

    $package_ids[] = 'recent';
    $enabled['recent'] = [];
  }

  // Determine what modules are new (within a week).
  $new_modules = _module_filter_new_modules();
  $package_ids[] = 'new';
  $enabled['new'] = [];

  $rows = [];
  $flip = ['even' => 'odd', 'odd' => 'even'];
  foreach (Element::children($form['modules']) as $package) {
    $package_id = _module_filter_get_id($package);
    $package_ids[] = $package_id;

    // Package title and header.
    $rows[] = [
      'data' => [
        [
          'data' => '<h3>' . $form['modules'][$package]['#title'] . '</h3>',
          'colspan' => 4
        ]
      ],
      'id' => $package_id . '-package',
      'class' => ['admin-package-title']
    ];
    $rows[] = [
      'data' => $header,
      'class' => ['admin-package-header']
    ];

    $stripe = 'odd';
    $enabled[$package_id] = [];
    foreach (Element::children($form['modules'][$package]) as $key) {
      $module = &$form['modules'][$package][$key];

      $is_enabled = isset($module['enable']['#default_value']) ? $module['enable']['#default_value'] : '';
      $enabled['all'][] = $enabled[$package_id][] = $is_enabled;
      if (isset($recent_modules[$key])) {
        $enabled['recent'][] = $is_enabled;
      }
      if (isset($new_modules[$key])) {
        $enabled['new'][] = $is_enabled;
      }

      $row = [];
      $version = !empty($module['version']);
      $requires = !empty($module['#requires']);
      $required_by = !empty($module['#required_by']);
      $toggle_enable = '';
      if (isset($module['enable']['#type']) && $module['enable']['#type'] == 'checkbox') {
        unset($module['enable']['#title']);
        $class = ($is_enabled ? 'enabled' : 'off');
        if (!empty($module['enable']['#disabled'])) {
          $class .= ' disabled';
        }
        $toggle_enable = '<div class="js-hide toggle-enable ' . $class . '"><div>&nbsp;</div></div>';
      }

      $row[] = [
        'class' => ['checkbox'],
        'data' => [
          '#markup' => $toggle_enable . $renderer->render($module['enable']),
          '#allowed_tags' => ['div', 'input']
        ]
      ];

      $label = '<label';
      if (isset($module['enable']['#id'])) {
        $label .= ' for="' . $module['enable']['#id'] . '"';
      }
      $row[] = [
        'class' => ['name'],
        'data' =>[
          '#markup' => $label . '><strong>' . $renderer->render($module['name']) . '</strong> <span class="module-machine-name">(' . $key . ')</span></label>'
        ]
      ];

      // Add the description, along with any modules it requires.
      $description = '<span class="details"><span class="text">' . $renderer->render($module['description']) . '</span></span>';
      if ($version || $requires || $required_by) {
        $description .= '<div class="requirements">';
        if ($version) {
          $description .= '<div class="admin-requirements">' . t('Version: @module-version', ['@module-version' => $renderer->render($module['version'])]) . '</div>';
        }
        if ($requires) {
          $requires = [
            '#theme' => 'item_list',
            '#items' => $module['#requires'],
            '#context' => ['list_style' => 'comma-list'],
          ];
          $module['requires'] = $renderer->render($requires);
          $description .= '<div class="admin-requirements">' . t('Requires: @module-list', ['@module-list' => $module['requires']]) . '</div>';
        }
        if ($required_by) {
          $required_by = [
            '#theme' => 'item_list',
            '#items' => $module['#required_by'],
            '#context' => ['list_style' => 'comma-list'],
          ];
          $module['required_by'] = $renderer->render($required_by);
          $description .= '<div class="admin-requirements">' . t('Required by: @module-list', ['@module-list' => $module['required_by']]) . '</div>';
        }
        $description .= '</div>';
      }
      $row[] = [
        'data' => [
          '#markup' => '<div class="inner expand" role="button">' . $description . '</div>'
        ],
        'class' => ['description']
      ];

      $operations= [];
      if (isset($module['links'])) {
        $links = $module['links'];
        foreach (Element::children($links) as $link_key) {
          if (!empty($links[$link_key]['#url'])) {
            $operations[] = [
              'title' => $links[$link_key]['#title'],
              'url' => $links[$link_key]['#url'],
            ];
          }
        }
      }

      $row[] = array(
        'data' => [
          '#type' => 'dropbutton',
          '#links' => $operations,
        ],
        'class' => array('links')
      );

      $class = [_module_filter_get_id($package) . '-tab', 'module', $stripe];
      if (isset($recent_modules[$key])) {
        $class[] = 'recent-module';
      }
      if (isset($new_modules[$key])) {
        $class[] = 'new-module';
      }
      $rows[] = [
        'data' => $row,
        'no_striping' => TRUE,
        'class' => $class
      ];
      $stripe = $flip[$stripe];
    }

    // Set the package as printed.
    $form['modules'][$package]['#printed'] = TRUE;
  }

  if ($config->get('module_filter_count_enabled')) {
    $enabled_counts = [];
    foreach ($enabled as $package_id => $value) {
      $enabled_counts[$package_id] = [
        'enabled' => count(array_filter($value)),
        'total' => count($value),
      ];
    }
    $form['module_filter']['#attached']['drupalSettings']['moduleFilter'] = [
      'packageIDs' => $package_ids,
      'enabledCounts' => $enabled_counts,
    ];
  }

  // Add first and last class to rows.
  $rows[0]['class'][] = 'first';
  $rows[count($rows) - 1]['class'][] = 'last';

  $variables['table'] = [
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
  ];
  unset($form['filters']);
  $variables['module_filter'] = $renderer->render($form['module_filter']);
  $variables['form_chilren'] = drupal_render_children($form);
}
