<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\TimeDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\field\Tests\reEnableModuleFieldTest;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector as BaseTimeDataCollector;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Class TimeDataCollector.
 */
class TimeDataCollector extends BaseTimeDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
   * @param $stopwatch
   */
  public function __construct(KernelInterface $kernel = NULL, $stopwatch = NULL) {
    parent::__construct($kernel, $stopwatch);
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    parent::collect($request, $response, $exception);

    $this->data['memory_limit'] = $this->convertToBytes(ini_get('memory_limit'));
    $this->updateMemoryUsage();
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    parent::lateCollect();

    $this->updateMemoryUsage();
  }

  /**
   * Gets the memory.
   *
   * @return int
   *   The memory
   */
  public function getMemory() {
    return $this->data['memory'];
  }

  /**
   * Gets the PHP memory limit.
   *
   * @return int
   *   The memory limit
   */
  public function getMemoryLimit() {
    return $this->data['memory_limit'];
  }

  /**
   * Updates the memory usage data.
   */
  public function updateMemoryUsage() {
    $this->data['memory'] = memory_get_peak_usage(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Timeline');
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return [
      'webprofiler/timeline',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDrupalSettings() {
    /** @var StopwatchEvent[] $collectedEvents */
    $collectedEvents = $this->getEvents();

    if (!empty($collectedEvents)) {
      $sectionPeriods = $collectedEvents['__section__']->getPeriods();
      $endTime = end($sectionPeriods)->getEndTime();
      $events = [];

      foreach ($collectedEvents as $key => $collectedEvent) {
        if ('__section__' != $key) {
          $periods = [];
          foreach ($collectedEvent->getPeriods() as $period) {
            $periods[] = [
              'start' => sprintf("%F", $period->getStartTime()),
              'end' => sprintf("%F", $period->getEndTime()),
            ];
          }

          $events[] = [
            "name" => $key,
            "category" => $collectedEvent->getCategory(),
            "origin" => sprintf("%F", $collectedEvent->getOrigin()),
            "starttime" => sprintf("%F", $collectedEvent->getStartTime()),
            "endtime" => sprintf("%F", $collectedEvent->getEndTime()),
            "duration" => sprintf("%F", $collectedEvent->getDuration()),
            "memory" => sprintf("%.1F", $collectedEvent->getMemory() / 1024 / 1024),
            "periods" => $periods,
          ];
        }
      }

      return ['time' => ['events' => $events, 'endtime' => $endTime]];
    }
    else {
      return ['time' => ['events' => [], 'endtime' => 0]];
    }
  }

  /**
   * @return array
   */
  public function getData() {
    $data = $this->data;

    $data['duration'] = $this->getDuration();
    $data['initTime'] = $this->getInitTime();

    return $data;
  }
}
