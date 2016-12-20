<?php

/**
 * @file
 * Contains \Drupal\module_filter\Form\ModuleFilterUpdateStatusForm.
 */

namespace Drupal\module_filter\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @todo.
 */
class ModuleFilterUpdateStatusForm extends FormBase {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'module_filter_update_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['module_filter'] = [
      '#type' => 'module_filter',
      '#attached' => [
        'library' => [
          'module_filter/drupal.module_filter.update_status',
        ],
      ],
    ];

    $show = \Drupal::request()->query->get('show', 'all');
    $form['module_filter']['show'] = [
      '#type' => 'radios',
      '#default_value' => in_array($show, ['all', 'updates', 'security', 'unknown']) ? $show : 'all',
      '#options' => [
        'all' => $this->t('All'),
        'updates' => $this->t('Update available'),
        'security' => $this->t('Security update'),
        'unknown' => $this->t('Unknown'),
      ],
      '#prefix' => '<div id="module-filter-show-wrapper">',
      '#suffix' => '</div>',
    ];
    if ($this->moduleHandler->moduleExists('update_advanced')) {
      $options = $form['module_filter']['show']['#options'];
      $form['module_filter']['show']['#options'] = array_slice($options, 0, 2);
      $form['module_filter']['show']['#options']['ignore'] = $this->t('Ignored from settings');
      $form['module_filter']['show']['#options'] = array_merge($form['module_filter']['show']['#options'], array_slice($options, 2));
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
