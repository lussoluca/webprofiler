<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\EventsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\DataCollector\EventDataCollector as BaseEventDataCollector;

/**
 * Class EventsDataCollector
 */
class EventsDataCollector extends BaseEventDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Events');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Called listeners: @listeners', ['@listeners' => count($this->getCalledListeners())]);
  }

  /**
   * @return int
   */
  public function getCalledListenersCount() {
    return count($this->getCalledListeners());
  }

  /**
   * @return int
   */
  public function getNotCalledListenersCount() {
    return count($this->getNotCalledListeners());
  }

//  /**
//   * @param $title
//   * @param $listeners
//   *
//   * @return mixed
//   */
//  private function getTable($title, $listeners) {
//    $build = array();
//
//    $rows = array();
//    foreach ($listeners as $listener) {
//      $row = array();
//      $row[] = $listener['event'];
//
//      if ($listener['type'] == 'Method') {
//        $data = array(
//          '#type' => 'inline_template',
//          '#template' => '{{ class }}::<a href="{{ link }}">{{ method }}</a>',
//          '#context' => array(
//            'class' => $this->abbrClass($listener['class']),
//            'link' => \Drupal::service('webprofiler.ide_link_generator')->generateLink($listener['file'], $listener['line']),
//            'method' => $listener['method']
//          ),
//        );
//
//        $row[] = render($data);
//      }
//      else {
//        $row[] = 'Closure';
//      }
//
//      $rows[] = $row;
//    }
//
//    $build['title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>{{ title }}</h3>',
//      '#context' => array(
//        'title' => $title,
//      ),
//    );
//
//    $build['table'] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => array(
//        $this->t('Event name'),
//        $this->t('Listener'),
//      ),
//      '#sticky' => TRUE,
//    );
//
//    return $build;
//  }
}
