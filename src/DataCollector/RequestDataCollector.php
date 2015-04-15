<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RequestDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\Controller\HtmlFormController;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Helper\IdeLinkGeneratorInterface;
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
   * @var \Drupal\webprofiler\Helper\IdeLinkGeneratorInterface
   */
  private $ideLinkGenerator;

  /**
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controllerResolver
   * @param \Drupal\webprofiler\Helper\IdeLinkGeneratorInterface $ideLinkGenerator
   */
  public function __construct(ControllerResolverInterface $controllerResolver, IdeLinkGeneratorInterface $ideLinkGenerator) {
    parent::__construct();

    $this->controllerResolver = $controllerResolver;
    $this->ideLinkGenerator = $ideLinkGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    parent::collect($request, $response, $exception);

    $controller = $this->controllerResolver->getController($request);

    try {
      $class = get_class($controller[0]);
      $method = new \ReflectionMethod($class, $controller[1]);

      $this->data['controller'] = array(
        'class' => is_object($controller[0]) ? $class : $controller[0],
        'method' => $controller[1],
        'file' => $this->ideLinkGenerator->generateLink($method->getFilename(), $method->getStartLine()),
      );
    } catch (\ReflectionException $re) {
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Request');
  }
}
