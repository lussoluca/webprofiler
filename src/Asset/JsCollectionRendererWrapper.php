<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Asset\JsCollectionRendererWrapper.
 */

namespace Drupal\webprofiler\Asset;

use Drupal\Core\Asset\JsCollectionRenderer;

/**
 * Class JsCollectionRendererWrapper.
 */
class JsCollectionRendererWrapper extends JsCollectionRenderer {

  /**
   * @var
   */
  private $jsAssets;

  /**
   * {@inheritdoc}
   */
  public function render(array $js_assets) {
    $this->jsAssets[] = $js_assets;

    return parent::render($js_assets);
  }

  /**
   * Returns the javascript assets.
   *
   * @return array
   *   The javascript assets.
   */
  public function getAssets() {
    return $this->jsAssets;
  }

}
