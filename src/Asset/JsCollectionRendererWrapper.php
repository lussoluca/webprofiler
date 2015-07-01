<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Asset\JsCollectionRendererWrapper.
 */

namespace Drupal\webprofiler\Asset;

use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\webprofiler\DataCollector\AssetDataCollector;

/**
 * Class JsCollectionRendererWrapper.
 */
class JsCollectionRendererWrapper implements AssetCollectionRendererInterface {

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
  public function render(array $js_assets) {
    $this->dataCollector->addJsAsset($js_assets);

    return $this->assetCollectionRenderer->render($js_assets);
  }
}
