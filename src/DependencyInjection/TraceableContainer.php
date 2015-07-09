<?php

/**
 * @file
 * Contains \Drupal\Core\DependencyInjection\TraceableContainer.
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
   * @param string $id
   * @param int $invalidBehavior
   *
   * @return object
   */
  public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
    Timer::start($id);
    $service = parent::get($id, $invalidBehavior);
    $this->tracedData[$id] = Timer::stop($id);

    return $service;
  }

  /**
   * @return array
   */
  public function getTracedData() {
    return $this->tracedData;
  }
}
