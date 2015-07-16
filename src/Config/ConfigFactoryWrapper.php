<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Config\ConfigFactoryWrapper.
 */

namespace Drupal\webprofiler\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\webprofiler\DataCollector\ConfigDataCollector;

/**
 * Wraps a config factory to be able to figure out all used config files.
 */
class ConfigFactoryWrapper implements ConfigFactoryInterface {

  /**
   * The wrapped config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config data collector.
   *
   * @var \Drupal\webprofiler\DataCollector\ConfigDataCollector
   */
  protected $configDataCollector;

  /**
   * Constructs a new ConfigFactoryWrapper.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\webprofiler\DataCollector\ConfigDataCollector $configDataCollector
   *   The config data collector.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ConfigDataCollector $configDataCollector) {
    $this->configFactory = $configFactory;
    $this->configDataCollector = $configDataCollector;
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    $result = $this->configFactory->get($name);
    $this->configDataCollector->addConfigName($name);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $names) {
    $result = $this->configFactory->loadMultiple($names);
    foreach (array_keys($result) as $name) {
      $this->configDataCollector->addConfigName($name);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function reset($name = NULL) {
    return $this->configFactory->reset($name);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($old_name, $new_name) {
    return $this->configFactory->rename($old_name, $new_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKeys() {
    return $this->configFactory->getCacheKeys();
  }

  /**
   * {@inheritdoc}
   */
  public function clearStaticCache() {
    return $this->configFactory->clearStaticCache();
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    return $this->configFactory->listAll($prefix);
  }

  /**
   * {@inheritdoc}
   */
  public function addOverride(ConfigFactoryOverrideInterface $config_factory_override) {
    return $this->configFactory->addOverride($config_factory_override);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditable($name) {
    return $this->configFactory->getEditable($name);
  }

  /**
   * @return array
   */
  public function __sleep() {
    return [];
  }
}
