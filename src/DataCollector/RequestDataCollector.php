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

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $header = array(
      $this->t('Key'),
      $this->t('Value')
    );

    // GET parameters
    if (count($this->getRequestQuery()->all()) > 0) {
      $build['get'] = $this->getTable($this->t('Request GET Parameters'), $this->getRequestQuery(), $header);
    }

    // POST parameters
    if (count($this->getRequestRequest()->all()) > 0) {
      $build['post'] = $this->getTable($this->t('Request POST Parameters'), $this->getRequestRequest(), $header);
    }

    // Attributes
    if (count($this->getRequestAttributes()->all()) > 0) {
      $build['attributes'] = $this->getTable($this->t('Request Attributes'), $this->getRequestAttributes(), $header);
    }

    // Cookies
    if (count($this->getRequestCookies()->all()) > 0) {
      $build['cookies'] = $this->getTable($this->t('Request Cookies'), $this->getRequestCookies(), $header);
    }

    // Headers
    $build['headers'] = $this->getTable($this->t('Request Headers'), $this->getRequestHeaders(), $header);

    // Content
    $build['content'] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>{{ title }}</h3>',
      '#context' => array(
        'title' => $this->t('Request Content'),
      ),
    );

    if (!$this->getContent()) {
      $build['content']['data'] = array(
        '#type' => 'inline_template',
        '#template' => '<h3>{{ message }}</h3>',
        '#context' => array(
          'message' => $this->t('No content'),
        ),
      );
    }
    else {
      $build['content']['data'] = array(
        '#type' => 'inline_template',
        '#template' => '{{ content }}',
        '#context' => array(
          'content' => $this->getContent(),
        ),
      );
    }

    // Server Parameters
    if (count($this->getRequestServer()->all()) > 0) {
      $build['server'] = $this->getTable($this->t('Request Server Parameters'), $this->getRequestServer(), $header);
    }

    // Response Headers
    if (count($this->getResponseHeaders()->all()) > 0) {
      $build['response-headers'] = $this->getTable($this->t('Response Headers'), $this->getResponseHeaders(), $header);
    }

    return $build;
  }
}
