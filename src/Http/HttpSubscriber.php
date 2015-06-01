<?php

namespace Drupal\webprofiler\Http;

use Drupal\webprofiler\DataCollector\HttpDataCollector;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Class HttpSubscriber
 */
class HttpSubscriber implements SubscriberInterface {

  /**
   * @var \Drupal\webprofiler\DataCollector\HttpDataCollector
   */
  private $httpDataCollector;

  /**
   * @param \Drupal\webprofiler\DataCollector\HttpDataCollector $httpDataCollector
   */
  public function __construct(HttpDataCollector $httpDataCollector) {
    $this->httpDataCollector = $httpDataCollector;
  }

  /**
   * {@inheritdoc}
   */
  public function getEvents() {
    return [
      'complete' => ['onComplete'],
      'error' => ['onError']
    ];
  }

  /**
   * @param \GuzzleHttp\Event\ErrorEvent $event
   * @param $name
   */
  public function onError(ErrorEvent $event, $name) {
    $request = $event->getRequest();
    $response = $event->getResponse();
    $transferInfo = $event->getTransferInfo();

    $httpEvent = new HttpEvent(
      $request->getUrl(),
      $request->getMethod(),
      $response->getStatusCode(),
      $request->getHeaders(),
      $response->getHeaders(),
      $transferInfo
    );

    $this->httpDataCollector->addError($httpEvent);
  }

  /**
   * @param \GuzzleHttp\Event\CompleteEvent $event
   * @param $name
   */
  public function onComplete(CompleteEvent $event, $name) {
    $request = $event->getRequest();
    $response = $event->getResponse();
    $transferInfo = $event->getTransferInfo();

    $httpEvent = new HttpEvent(
      $request->getUrl(),
      $request->getMethod(),
      $response->getStatusCode(),
      $request->getHeaders(),
      $response->getHeaders(),
      $transferInfo
    );

    $this->httpDataCollector->addCompleted($httpEvent);
  }
}
