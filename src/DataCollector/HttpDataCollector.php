<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\HttpDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Http\Client;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Http\HttpEvent;
use Drupal\webprofiler\Http\HttpSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about http calls during request.
 */
class HttpDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Http\Client
   */
  private $client;

  /**
   * @param \Drupal\Core\Http\Client $client
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $this->client->attach(new HttpSubscriber($this));

    $this->data['completed'] = array();
    $this->data['error'] = array();
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * @param \Drupal\webprofiler\Http\HttpEvent $event
   */
  public function addCompleted(HttpEvent $event) {
    $this->data['completed'][] = $event;
  }

  /**
   * @param \Drupal\webprofiler\Http\HttpEvent $event
   */
  public function addError(HttpEvent $event) {
    $this->data['error'][] = $event;
  }

  /**
   * @return int
   */
  public function getCompletedCount() {
    return count($this->data['completed']);
  }

  /**
   * @return HttpEvent[]
   */
  public function getCompleted() {
    return $this->data['completed'];
  }

  /**
   * @return int
   */
  public function getErrorCount() {
    return count($this->data['error']);
  }

  /**
   * @return HttpEvent[]
   */
  public function getError() {
    return $this->data['error'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'http';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Http');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Completed @completed, error @error', array(
      '@completed' => $this->getCompletedCount(),
      '@error' => $this->getErrorCount()
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $build += $this->getTable($this->getCompleted(), $this->t('Completed'));
    $build += $this->getTable($this->getError(), $this->t('Error'));

    return $build;
  }

  /**
   * @param HttpEvent[] $requests
   * @param string $type
   *
   * @return array
   */
  private function getTable($requests, $type) {
    $rows = array();
    foreach ($requests as $request) {
      $row = array();

      $row[] = $request->getUrl();
      $row[] = $request->getMethod();
      $row[] = $request->getStatusCode();
      $row[] = $this->varToString($request->getRequestHeaders());
      $row[] = $this->varToString($request->getResponseHeaders(), TRUE);
      $row[] = $this->varToString($request->getTransferInfo(), TRUE);

      $rows[] = $row;
    }

    $header = array(
      $this->t('Url'),
      $this->t('Method'),
      $this->t('Status code'),
      array(
        'data' => $this->t('Request headers'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      array(
        'data' => $this->t('Response headers'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      array(
        'data' => $this->t('Transfer info'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
    );

    $build['title_' . $type] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>{{ title }}</h3>',
      '#context' => array(
        'title' => $type,
      ),
    );

    $build['table_' . $type] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#sticky' => TRUE,
    );

    return $build;
  }
}
