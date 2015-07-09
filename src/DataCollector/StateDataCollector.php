<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\StateDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector to get all requested state values.
 */
class StateDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * @param $key
   */
  public function addState($key) {
    $this->data['state_get'][$key] = isset($this->data['state_get'][$key]) ? $this->data['state_get'][$key] + 1 : 1;
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
    return $this->t('State variables: @variables', ['@variables' => count($this->stateKeys())]);
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
