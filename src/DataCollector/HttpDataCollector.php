<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\HttpDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\Http\HttpClientMiddleware;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about http calls during request.
 */
class HttpDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \GuzzleHttp\Client
   */
  private $middleware;

  /**
   * @param \Drupal\webprofiler\Http\HttpClientMiddleware $middleware
   */
  public function __construct(HttpClientMiddleware $middleware) {
    $this->middleware = $middleware;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['completed'] = $this->middleware->getCompletedRequests();
    $this->data['failed'] = $this->middleware->getFailedRequests();
  }

  /**
   * @return int
   */
  public function getCompletedRequestsCount() {
    return count($this->getCompletedRequests());
  }

  /**
   * @return array
   */
  public function getCompletedRequests() {
    return $this->data['completed'];
  }

  /**
   * @return int
   */
  public function getFailedRequestsCount() {
    return count($this->getFailedRequests());
  }

  /**
   * @return array
   */
  public function getFailedRequests() {
    return $this->data['failed'];
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
    return $this->t('Completed @completed, error @error', [
      '@completed' => $this->getCompletedRequestsCount(),
      '@error' => $this->getFailedRequestsCount()
    ]);
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    $build = array();
//
//    $build += $this->getTable($this->getCompletedRequests(), $this->t('Completed'), 'completed');
//    $build += $this->getTable($this->getFailedRequests(), $this->t('Error'), 'failure');
//
//    return $build;
//  }
//
//  /**
//   * @param array $calls
//   * @param string $type
//   *
//   * @return array
//   */
//  private function getTable($calls, $title, $type) {
//    $rows = array();
//
//    foreach ($calls as $call) {
//      /** @var \Psr\Http\Message\RequestInterface $request */
//      $request = $call['request'];
//
//      /** @var \Psr\Http\Message\ResponseInterface $response */
//      $response = isset($call['response']) ? $call['response'] : NULL;
//
//      $row = array();
//
//      $row[] = $request->getUri();
//      $row[] = $request->getMethod();
//
//      if ($type == 'completed') {
//        $row[] = $response->getStatusCode();
//        $row[] = $this->varToString($request->getHeaders());
//        $row[] = $this->varToString($response->getHeaders(), TRUE);
//      } else {
//        $row[] = $call['message'];
//      }
//
//      $rows[] = $row;
//    }
//
//    $header = array(
//      $this->t('Url'),
//      $this->t('Method'),
//    );
//
//    if ($type == 'completed') {
//      $header[] = $this->t('Status code');
//      $header[] = array(
//        'data' => $this->t('Request headers'),
//        'class' => array(RESPONSIVE_PRIORITY_LOW),
//      );
//      $header[] = array(
//        'data' => $this->t('Response headers'),
//        'class' => array(RESPONSIVE_PRIORITY_LOW),
//      );
//    } else {
//      $header[] = array(
//        'data' => $this->t('Message'),
//        'class' => array(RESPONSIVE_PRIORITY_LOW),
//      );
//    }
//
//    $build['title_' . $type] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>{{ title }}</h3>',
//      '#context' => array(
//        'title' => $title,
//      ),
//    );
//
//    $build['table_' . $type] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => $header,
//      '#sticky' => TRUE,
//    );
//
//    return $build;
//  }
}
