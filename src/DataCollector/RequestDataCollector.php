<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RequestDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * Integrate _content into the RequestDataCollector
 */
class RequestDataCollector extends BaseRequestDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  private $controllerResolver;

  /**
   * @var array
   */
  private $accessCheck;

  /**
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controllerResolver
   */
  public function __construct(ControllerResolverInterface $controllerResolver) {
    parent::__construct();

    $this->controllerResolver = $controllerResolver;
    $this->accessCheck = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    parent::collect($request, $response, $exception);

    $controller = $this->controllerResolver->getController($request);

    $this->data['controller'] = $this->getMethodData($controller[0], $controller[1]);
    $this->data['access_check'] = $this->accessCheck;
  }

  /**
   * @param $service_id
   * @param $callable
   * @param $request
   */
  public function addAccessCheck($service_id, $callable, Request $request) {
    $this->accessCheck[$request->getPathInfo()][] = [
      'service_id' => $service_id,
      'callable' => $this->getMethodData($callable[0], $callable[1]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Request');
  }
}
