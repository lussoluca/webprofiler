<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DependencyInjection\TraceableContainer;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ServiceDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Symfony\Component\DependencyInjection\IntrospectableContainerInterface
   *   $container
   */
  private $container;

  /**
   * @param IntrospectableContainerInterface $container
   */
  public function __construct(IntrospectableContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['initialized_services'] = array();
    if ($this->getServicesCount()) {
      foreach (array_keys($this->getServices()) as $id) {
        if ($this->container->initialized($id)) {
          $this->data['initialized_services'][] = $id;
        }
      }
    }

    if ($this->container instanceof TraceableContainer) {
      $this->data['times'] = $this->container->getTracedData();
    }
  }

  /**
   * @param $graph
   */
  public function setServicesGraph($graph) {
    $this->data['graph'] = $graph;
  }

  /**
   * @return int
   */
  public function getServicesCount() {
    return count($this->data['graph']);
  }

  /**
   * @return int
   */
  public function getInitializedServicesCount() {
    return count($this->data['initialized_services']);
  }

  /**
   * @return int
   */
  public function getInitializedServicesWithoutWebprofilerCount() {
    $countWithoutWebprofiler = 0;
    foreach ($this->data['initialized_services'] as $service) {
      if (strpos($service, 'webprofiler') !== 0) {
        $countWithoutWebprofiler++;
      }
    }
    return $countWithoutWebprofiler;
  }

  /**
   * @return array
   */
  public function getServices() {
    return $this->data['graph'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'service';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Service');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Initialized: @count, initialized without Webprofiler: @count_without_webprofiler, available: @available', array(
      '@count' => $this->getInitializedServicesCount(),
      '@count_without_webprofiler' => $this->getInitializedServicesWithoutWebprofilerCount(),
      '@available' => $this->getServicesCount()
    ));
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    $build = array();
//
//    $build['filters'] = \Drupal::formBuilder()
//      ->getForm('Drupal\\webprofiler\\Form\\ServiceFilterForm');
//
//    $build['container'] = array(
//      '#type' => 'container',
//      '#attributes' => array('id' => array('wp-service-wrapper')),
//    );
//
//    if ($this->getServicesCount()) {
//      $rows = array();
//      $services = $this->getServices();
//      ksort($services);
//
//      foreach ($services as $id => $service) {
//        $row = array();
//
//        $row[] = $id;
//
//        $class = $service['value']['class'];
//        $row[] = $class;
//
//        $edges = array();
//        foreach ($service['outEdges'] AS $edge) {
//          $edges[] = $edge['id'];
//        }
//
//        $initialized = in_array($id, $this->data['initialized_services']);
//        $row[] = ($initialized) ? $this->t('Yes') : $this->t('No');
//
//        $dependsOn = implode(', ', $edges);
//        $row[] = $dependsOn;
//
//        $tags = array();
//        foreach ($service['value']['tags'] AS $tag => $value) {
//          $tags[] = $tag;
//        }
//
//        $implodedTags = implode(', ', $tags);
//        $row[] = $implodedTags;
//
//        $rows[] = array(
//          'data' => $row,
//          'data-wp-service-id' => $id,
//          'data-wp-service-class' => $class,
//          'data-wp-service-initialized' => ($initialized) ? 1 : 0,
//          'data-wp-service-depends-on' => $dependsOn,
//          'data-wp-service-tags' => $implodedTags,
//        );
//      }
//
//      $header = array(
//        $this->t('Id'),
//        $this->t('Class'),
//        $this->t('Initialized'),
//        $this->t('Depends on'),
//        $this->t('Tags'),
//      );
//
//      $build['container']['table'] = array(
//        '#type' => 'table',
//        '#rows' => $rows,
//        '#header' => $header,
//        '#sticky' => TRUE,
//        '#attached' => array(
//          'library' => array(
//            'webprofiler/service',
//          ),
//        ),
//        '#attributes' => array(
//          'class' => array('wp-service-table'),
//        ),
//      );
//    }
//
//    return $build;
//  }
}
