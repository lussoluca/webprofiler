<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\CacheDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects the used cache bins and cache CIDs.
 */
class CacheDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Cache');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return \Drupal::translation()->translate('Total cache: @cache', array('@cache' => $this->countCacheCids()));
  }

  /**
   * Registers a cache get call on a specific cache bin.
   */
  public function registerCacheGet($bin, $cid) {
    $this->data['bin_cids'][$bin][$cid] = isset($this->data[$bin][$cid]) ? $this->data[$bin][$cid] + 1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'cache';
  }

  /**
   * Twig callback to return the total amount of requested cache CIDS.
   *
   * @return int
   */
  public function countCacheCids() {
    $total_count = 0;
    foreach ($this->data['bin_cids'] as $cids) {
      $total_count += count($cids);
    }
    return $total_count;
  }

  /**
   * Twig callback to return all registered cache CIDs keyed by bin.
   *
   * @return array
   */
  public function cacheCids() {
    return $this->data['bin_cids'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    foreach ($this->cacheCids() as $bin => $cids) {
      $build[$bin] = $this->getTable($bin . ' (' . count($cids) . ')', $cids, array($this->t('cid'), $this->t('hits')));
    }

    return $build;
  }

}
