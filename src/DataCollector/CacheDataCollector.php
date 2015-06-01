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

  const WEBPROFILER_CACHE_HIT = 'bin_cids_hit';
  const WEBPROFILER_CACHE_MISS = 'bin_cids_miss';

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   *
   */
  public function __construct() {
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_HIT] = 0;
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_MISS] = 0;
    $this->data['cache'] = array();
  }

  /**
   * Registers a cache get on a specific cache bin.
   *
   * @param $cache
   */
  public function registerCacheHit($bin, $cache) {
    $current = isset($this->data['cache'][$bin][$cache->cid]) ? $this->data['cache'][$bin][$cache->cid] : NULL;

    if (!$current) {
      $current = $cache;
      $current->{CacheDataCollector::WEBPROFILER_CACHE_HIT} = 0;
      $current->{CacheDataCollector::WEBPROFILER_CACHE_MISS} = 0;
      $this->data['cache'][$bin][$cache->cid] = $current;
    }

    $current->{CacheDataCollector::WEBPROFILER_CACHE_HIT}++;
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_HIT]++;
  }

  /**
   * Registers a cache get on a specific cache bin.
   *
   * @param $bin
   * @param $cid
   */
  public function registerCacheMiss($bin, $cid) {
    $current = isset($this->data['cache'][$bin][$cid]) ?
      $this->data['cache'][$bin][$cid] : NULL;

    if (!$current) {
      $current = new \StdClass();
      $current->{CacheDataCollector::WEBPROFILER_CACHE_HIT} = 0;
      $current->{CacheDataCollector::WEBPROFILER_CACHE_MISS} = 0;
      $this->data['cache'][$bin][$cid] = $current;
    }

    $current->{CacheDataCollector::WEBPROFILER_CACHE_MISS}++;
    $this->data['total'][CacheDataCollector::WEBPROFILER_CACHE_MISS]++;
  }

  /**
   * Callback to return the total amount of requested cache CIDS.
   *
   * @param string $type
   *
   * @return int
   */
  public function countCacheCids($type) {
   return $this->data['total'][$type];
  }

  /**
   * Callback to return the total amount of hit cache CIDS.
   *
   * @return int
   */
  public function countCacheHits() {
    return $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT);
  }

  /**
   * Callback to return the total amount of miss cache CIDS.
   *
   * @return int
   */
  public function countCacheMisses() {
    return $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS);
  }

  /**
   * Callback to return the total amount of hit cache CIDs keyed by bin.
   *
   * @param $type
   *
   * @return array
   */
  public function cacheCids($type) {
    $hits = array();
    foreach ($this->data['cache'] as $bin => $caches) {
      $hits[$bin] = 0;
      foreach($caches as $cid => $cache) {
        $hits[$bin] += $cache->{$type};
      }
    }

    return $hits;
  }

  /**
   * Callback to return hit cache CIDs keyed by bin.
   *
   * @return array
   */
  public function cacheHits() {
    return $this->cacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT);
  }

  /**
   * Callback to return miss cache CIDs keyed by bin.
   *
   * @return array
   */
  public function cacheMisses() {
    return $this->cacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'cache';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Cache');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total cache hit: @cache_hit, total cache miss: @cache_miss', array(
      '@cache_hit' => $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT),
      '@cache_miss' => $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    foreach ($this->data['cache'] as $bin => $caches) {
      $rows = array();

      $build[$bin . '_title'] = array(
        '#type' => 'inline_template',
        '#template' => '<h3>{{ key }} ({{ totalNum }})</h3>',
        '#context' => array(
          'key' => $bin,
          'totalNum' => count($caches),
        ),
      );

      foreach($caches as $cid => $cache) {
        $row = array();

        $row[] = $cid;
        $row[] = $cache->{CacheDataCollector::WEBPROFILER_CACHE_HIT};
        $row[] = $cache->{CacheDataCollector::WEBPROFILER_CACHE_MISS};
        $row[] = isset($cache->tags) ? implode(', ', $cache->tags) : '';

        $rows[] = $row;
      }

      $header = array(
        array(
          'data' => $this->t('cid'),
          'class' => array('cache-data-cid'),
        ),
        array(
          'data' => $this->t('hits'),
          'class' => array('cache-data-hit'),
        ),
        array(
          'data' => $this->t('misses'),
          'class' => array('cache-data-miss'),
        ),
        array(
          'data' => $this->t('tags'),
          'class' => array('cache-data-tags'),
        ),
      );

      $build[$bin . '_table'] = array(
        '#type' => 'table',
        '#rows' => $rows,
        '#header' => $header,
        '#attributes' => array('class' => array('cache-data')),
        '#sticky' => TRUE,
      );
    }

    return $build;
  }
}
