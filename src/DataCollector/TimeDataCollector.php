<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\TimeDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Component\Utility\SafeMarkup;
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
  public function getPanel() {
    $rows = array(
      array(
        $this->t('Total time'),
        SafeMarkup::format('!duration ms',
          array('!duration' => sprintf('%.0f', $this->getDuration()))),
      ),
      array(
        $this->t('Initialization time'),
        SafeMarkup::format('!duration ms',
          array('!duration' => sprintf('%.0f', $this->getInitTime()))),
      ),
    );

    return array(
      '#type' => 'table',
      '#rows' => $rows,
      '#sticky' => TRUE,
      '#attached' => array(
        'drupalSettings' => array(
          'webprofiler' => $this->getAttachedJs(),
        ),
        'library' => array(
          'webprofiler/timeline',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  private function getAttachedJs() {
    /** @var StopwatchEvent[] $collectedEvents */
    $collectedEvents = $this->getEvents();
    $sectionPeriods = $collectedEvents['__section__']->getPeriods();
    $endTime = end($sectionPeriods)->getEndTime();
    $events = array();

    foreach ($collectedEvents as $key => $collectedEvent) {
      if ('__section__' != $key) {
        $periods = array();
        foreach ($collectedEvent->getPeriods() as $period) {
          $periods[] = array(
            'start' => sprintf("%F", $period->getStartTime()),
            'end' => sprintf("%F", $period->getEndTime()),
          );
        }

        $events[] = array(
          "name" => $key,
          "category" => $collectedEvent->getCategory(),
          "origin" => sprintf("%F", $collectedEvent->getOrigin()),
          "starttime" => sprintf("%F", $collectedEvent->getStartTime()),
          "endtime" => sprintf("%F", $collectedEvent->getEndTime()),
          "duration" => sprintf("%F", $collectedEvent->getDuration()),
          "memory" => sprintf("%.1F", $collectedEvent->getMemory() / 1024 / 1024),
          "periods" => $periods,
        );
      }
    }

    return array('time' => array('events' => $events, 'endtime' => $endTime));
  }

  /**
   * @param $memoryLimit
   *
   * @return int|string
   */
  private function convertToBytes($memoryLimit) {
    if ('-1' === $memoryLimit) {
      return -1;
    }

    $memoryLimit = strtolower($memoryLimit);
    $max = strtolower(ltrim($memoryLimit, '+'));
    if (0 === strpos($max, '0x')) {
      $max = intval($max, 16);
    }
    elseif (0 === strpos($max, '0')) {
      $max = intval($max, 8);
    }
    else {
      $max = intval($max);
    }

    switch (substr($memoryLimit, -1)) {
      case 't':
        $max *= 1024;
        break;

      case 'g':
        $max *= 1024;
        break;

      case 'm':
        $max *= 1024;
        break;

      case 'k':
        $max *= 1024;
        break;
    }

    return $max;
  }

}
