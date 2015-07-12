<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\EventsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\DataCollector\EventDataCollector as BaseEventDataCollector;

/**
 * Class EventsDataCollector
 */
class EventsDataCollector extends BaseEventDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Events');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Called listeners: @listeners', ['@listeners' => count($this->getCalledListeners())]);
  }

  /**
   * @return int
   */
  public function countCalledListeners() {
    return count($this->getCalledListeners());
  }

  /**
   * @return int
   */
  public function countNotCalledListeners() {
    return count($this->getNotCalledListeners());
  }
}
