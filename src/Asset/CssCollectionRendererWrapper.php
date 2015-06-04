<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Asset\CssCollectionRendererWrapper.
 */

namespace Drupal\webprofiler\Asset;

use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\webprofiler\DataCollector\AssetDataCollector;

/**
 * Class CssCollectionRendererWrapper.
 */
class CssCollectionRendererWrapper implements AssetCollectionRendererInterface {

  /**
   * @var \Drupal\Core\Asset\AssetCollectionRendererInterface
   */
  private $assetCollectionRenderer;

  /**
   * @var \Drupal\webprofiler\DataCollector\AssetDataCollector
   */
  private $dataCollector;

  /**
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $assetCollectionRenderer
   * @param \Drupal\webprofiler\DataCollector\AssetDataCollector $dataCollector
   */
  public function __construct(AssetCollectionRendererInterface $assetCollectionRenderer, AssetDataCollector $dataCollector) {
    $this->assetCollectionRenderer = $assetCollectionRenderer;
    $this->dataCollector = $dataCollector;
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $css_assets) {
    $this->dataCollector->addCssAsset($css_assets);

    return $this->assetCollectionRenderer->render($css_assets);
  }
}
