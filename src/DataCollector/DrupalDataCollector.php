<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class DrupalDataCollector
 */
class DrupalDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * Collects data for the given Request and Response.
   *
   * @param Request $request A Request instance
   * @param Response $response A Response instance
   * @param \Exception $exception An Exception instance
   *
   * @api
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['version'] = \Drupal::VERSION;
    $this->data['profile'] = drupal_get_profile();
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->data['version'];
  }

  /**
   * @return string
   */
  public function getProfile() {
    return $this->data['profile'];
  }

  /**
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'drupal';
  }

  public function getPanel() {
    return array(
      '#markup' => 'pippo',
    );
  }
}
