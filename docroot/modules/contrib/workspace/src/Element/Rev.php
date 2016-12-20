<?php

namespace Drupal\workspace\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for a items in a revision tree.
 *
 * @RenderElement("rev")
 */
class Rev extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#pre_render' => array(
        array($class, 'preRenderRev'),
      ),
    );
  }

  /**
   * Pre-render callback.
   */
  public static function preRenderRev($element) {
    $info = array(
      '#uuid' => $element['#uuid'],
      '#rev' => $element['#rev'],
      '#rev_info' => $element['#rev_info'],
      '#theme' => 'workspace_rev',
    );

    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = \Drupal::service('renderer');
    $element['#markup'] = $renderer->render($info);
    return $element;
  }

}
