<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Asset\CssCollectionRendererWrapper.
 */

namespace Drupal\webprofiler\Asset;

use Drupal\Core\Asset\CssCollectionRenderer;

/**
 * Class CssCollectionRendererWrapper.
 */
class CssCollectionRendererWrapper extends CssCollectionRenderer {

  /**
   * @var
   */
  private $cssAssets;

  /**
   * {@inheritdoc}
   */
  public function render(array $css_assets) {
    $this->cssAssets[] = $css_assets;

    return parent::render($css_assets);
  }

  /**
   * Returns the css assets.
   *
   * @return array
   */
  public function getAssets() {
    return $this->cssAssets;
  }

}
