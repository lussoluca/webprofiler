<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\String;

/**
 * Class DrupalDataCollectorTrait
 */
trait DrupalDataCollectorTrait {

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPanel() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getDrupalSettings() {
    return array();
  }

  /**
   * @return mixed
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @param $value
   *
   * @return int|string
   */
  private function convertToBytes($value) {
    if ('-1' === $value) {
      return -1;
    }

    $value = strtolower($value);
    $max = strtolower(ltrim($value, '+'));
    if (0 === strpos($max, '0x')) {
      $max = intval($max, 16);
    }
    elseif (0 === strpos($max, '0')) {
      $max = intval($max, 8);
    }
    else {
      $max = intval($max);
    }

    switch (substr($value, -1)) {
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
