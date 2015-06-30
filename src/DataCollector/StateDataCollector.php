<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\StateDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector to get all requested state values.
 */
class StateDataCollector extends DataCollector implements StateInterface, DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new StateDataCollector.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * Twig callback to show all requested state keys.
   */
  public function stateKeys() {
    return $this->data['state_get'];
  }

  /**
   * {@inheritdoc}
   */
  public function get($key, $default = NULL) {
    $this->data['state_get'][$key] = isset($this->data['state_get'][$key]) ? $this->data['state_get'][$key] + 1 : 1;
    return $this->state->get($key, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    foreach ($keys as $key) {
      $this->data['state_get'][$key] = isset($this->data['state_get'][$key]) ? $this->data['state_get'][$key] + 1 : 1;
    }
    return $this->state->getMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    return $this->state->set($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data) {
    return $this->state->setMultiple($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    return $this->state->delete($key);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    return $this->state->resetCache();
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    return $this->state->resetCache();
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('State');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'state';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('State variables: @variables', array('@variables' => count($this->stateKeys())));
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    // State
//    $build['state'] = $this->getTable($this->t('State variables used'), $this->stateKeys(), array(
//      $this->t('id'),
//      $this->t('get')
//    ));
//
//    return $build;
//  }
}
