<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DependencyInjection\TraceableContainer.
 */

namespace Drupal\webprofiler\DependencyInjection;

use Drupal\Component\Utility\Timer;
use Drupal\Core\DependencyInjection\Container;

/**
 * Extends the Drupal container class to trace service instantiations.
 */
class TraceableContainer extends Container {

  /**
   * @var array
   */
  protected $tracedData;

  /**
   * @var \Symfony\Component\Stopwatch\Stopwatch
   */
  private $stopwatch = NULL;

  /**
   * @param string $id
   * @param int $invalidBehavior
   *
   * @return object
   */
  public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
    if(!$this->stopwatch) {
      $this->stopwatch = call_user_func([$this, $this->methodMap['stopwatch']]);
      $this->stopwatch->openSection();
    }

    if('stopwatch' === $id) {
      return $this->stopwatch;
    }

    Timer::start($id);
    $e = $this->stopwatch->start($id, 'service');

    $service = parent::get($id, $invalidBehavior);

    $this->tracedData[$id] = Timer::stop($id);
    if($e->isStarted()) {
      $e->stop();
    }

    return $service;
  }

  /**
   * @return array
   */
  public function getTracedData() {
    return $this->tracedData;
  }
}
