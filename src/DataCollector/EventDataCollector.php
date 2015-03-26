<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\EventDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\String;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Helper\IdeLinkGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\DataCollector\EventDataCollector as BaseEventDataCollector;

/**
 * Class EventDataCollector
 */
class EventDataCollector extends BaseEventDataCollector implements DrupalDataCollectorInterface {

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
    return $this->t('Called listeners: @listeners', array('@listeners' => count($this->getCalledListeners())));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $calledListeners = $this->getCalledListeners();
    $notCalledListeners = $this->getNotCalledListeners();

    $build = array();

    if (empty($calledListeners)) {
      $build['no-events'] = array(
        '#type' => 'inline_template',
        '#template' => '{{ message }}',
        '#context' => array(
          'message' => $this->t('No events have been recorded. Are you sure that debugging is enabled in the kernel?'),
        ),
      );

      return $build;
    }

    // Called listeners
    $build['called'] = $this->getTable($this->t('Called listeners'), $calledListeners);

    // Non called listeners
    $build['non-called'] = $this->getTable($this->t('Non called listeners'), $notCalledListeners);

    return $build;
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

  /**
   * @param $title
   * @param $listeners
   *
   * @return mixed
   */
  private function getTable($title, $listeners) {
    $build = array();

    $rows = array();
    foreach ($listeners as $listener) {
      $row = array();
      $row[] = $listener['event'];

      if ($listener['type'] == 'Method') {
        $data = array(
          '#type' => 'inline_template',
          '#template' => '{{ class }}::<a href="{{ link }}">{{ method }}</a>',
          '#context' => array(
            'class' => $this->abbrClass($listener['class']),
            'link' => \Drupal::service('webprofiler.ide_link_generator')->generateLink($listener['file'], $listener['line']),
            'method' => $listener['method']
          ),
        );

        $row[] = render($data);
      }
      else {
        $row[] = 'Closure';
      }

      $rows[] = $row;
    }

    $build['title'] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>{{ title }}</h3>',
      '#context' => array(
        'title' => $title,
      ),
    );

    $build['table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => array(
        $this->t('Event name'),
        $this->t('Listener'),
      ),
      '#sticky' => TRUE,
    );

    return $build;
  }

  /**
   * @param $class
   *
   * @return string
   */
  private function abbrClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    return String::format("<abbr title=\"@class\">@short</abbr>", array('@class' => $class, '@short' => $short));
  }
}
