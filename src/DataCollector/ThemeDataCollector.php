<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\ThemeDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\Theme\ThemeNegotiatorWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * Class ThemeDataCollector
 */
class ThemeDataCollector extends DataCollector implements DrupalDataCollectorInterface, LateDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * @var \Drupal\Core\Theme\ThemeNegotiatorInterface
   */
  private $themeNegotiator;

  /**
   * @var \Twig_Profiler_Profile
   */
  private $profile;

  /**
   * @var
   */
  private $computed;

  /**
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   * @param \Drupal\Core\Theme\ThemeNegotiatorInterface $themeNegotiator
   * @param \Twig_Profiler_Profile $profile
   */
  public function __construct(ThemeManagerInterface $themeManager, ThemeNegotiatorInterface $themeNegotiator, \Twig_Profiler_Profile $profile) {
    $this->themeManager = $themeManager;
    $this->themeNegotiator = $themeNegotiator;
    $this->profile = $profile;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $activeTheme = $this->themeManager->getActiveTheme();

    $this->data['activeTheme'] = [
      'name' => $activeTheme->getName(),
      'path' => $activeTheme->getPath(),
      'engine' => $activeTheme->getEngine(),
      'owner' => $activeTheme->getOwner(),
      'baseThemes' => $activeTheme->getBaseThemes(),
      'extension' => $activeTheme->getExtension(),
      'styleSheetsRemove' => $activeTheme->getStyleSheetsRemove(),
      'libraries' => $activeTheme->getLibraries(),
      'regions' => $activeTheme->getRegions(),
    ];

    if ($this->themeNegotiator instanceof ThemeNegotiatorWrapper) {
      $this->data['negotiator'] = [
        'class' => $this->getMethodData($this->themeNegotiator->getNegotiator(), 'determineActiveTheme'),
        'id' => $this->themeNegotiator->getNegotiator()->_serviceId,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    $this->data['twig'] = serialize($this->profile);
  }

  /**
   * @return string
   */
  public function getActiveTheme() {
    return $this->data['activeTheme'];
  }

  /**
   * @return array
   */
  public function getThemeNegotiator() {
    return $this->data['negotiator'];
  }

  /**
   * @return int
   */
  public function getTime() {
    return $this->getProfile()->getDuration() * 1000;
  }

  /**
   * @return mixed
   */
  public function getTemplateCount() {
    return $this->getComputedData('template_count');
  }

  /**
   * @return mixed
   */
  public function getTemplates() {
    return $this->getComputedData('templates');
  }

  /**
   * @return mixed
   */
  public function getBlockCount() {
    return $this->getComputedData('block_count');
  }

  /**
   * @return mixed
   */
  public function getMacroCount() {
    return $this->getComputedData('macro_count');
  }

  /**
   * @return \Twig_Markup
   */
  public function getHtmlCallGraph() {
    $dumper = new \Twig_Profiler_Dumper_Html();

    return new \Twig_Markup($dumper->dump($this->getProfile()), 'UTF-8');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'theme';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Theme');
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon() {
    return 'iVBORw0KGgoAAAANSUhEUgAAABUAAAAcCAYAAACOGPReAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYxIDY0LjE0MDk0OSwgMjAxMC8xMi8wNy0xMDo1NzowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIE1hY2ludG9zaCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDoxQUE0NEI2NTlCQTkxMUUzQkFDRjg2NUVCQ0NFNTcwQiIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDoxQUE0NEI2NjlCQTkxMUUzQkFDRjg2NUVCQ0NFNTcwQiI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjFBQTQ0QjYzOUJBOTExRTNCQUNGODY1RUJDQ0U1NzBCIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjFBQTQ0QjY0OUJBOTExRTNCQUNGODY1RUJDQ0U1NzBCIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+C1mVdgAAAktJREFUeNrUlk+I6VEUx48/4SVKpkgiCywkK8ksWVhYSFm9hYXFqxfbt2KnLJS9hbKQhZXtFMVmUrKYkZmslPxLSvnvYZx37+2NTDPP03ss5tTpcu/xued77rm/Hw4iwqWNC1cwzv8CPlJ6lUypfSf+k256Ad8R/0HlL4l/uWCSL5zfO/xTLTkczrvx9aDwU7QUX61Ww/Pz88WAdrv9Oplyzz0UPp8PIpEIzrnWJ6EUJBAI2DgYDOD+/h7EYjFwuadz4X80SUFCoRB6vR6Mx2Mwm82QTqfh4eEBNBoN3NzcgFQqhdVqBbvd7s+ZUlkUJpFIoN1uQyQSAYvFAqlUiq2Xy2VwuVwQCoXAZrNBMpmE6XTK4nk83lsqOX0ki0h2xUajgYFAAJVKJRoMBoxGo9hqtXA4HCKNq1ar+Pj4iMFgEFUqFer1egyHw9jv93GxWDCOyWTCA5RaLBZjd9jhcGCz2cRXKxQKqNVqcTQaHeZKpRLqdDoWf3d3h6QMB+hB/nK5BL/fD4lEAsiPmUSfzwf1eh1qtRoYjUaQy+WQz+eBbAoej4fVN5PJgNVqZfV9J586lUBtMplgLpdDEoyktuj1ejEej7M1p9OJbrcbi8UirtdrNjebzQ6MN/KPfT6f436/Z3XudDp4e3uLlUqFQej37XbL5B7DjqEfthQBAgGzfpTJZJDNZpn0zWbDRgI/eQlOdjGFU1coFIfHGu3Lv90qbrfbXRFpJ4POAVGjh/r09PRCP38l3r3Q62RA/NtV3qb8T/Nn4irQXwIMANMNuV/Q8qbhAAAAAElFTkSuQmCC';
  }

  /**
   * @return array
   */
  public function getData() {
    $data = $this->data;

    $data['twig'] = [
      'callgraph' => (string) $this->getHtmlCallGraph(),
      'render_time' => $this->getTime(),
      'template_count' => $this->getTemplateCount(),
      'templates' => $this->getTemplates(),
      'block_count' => $this->getBlockCount(),
      'macro_count' => $this->getMacroCount(),
    ];

    return $data;
  }

  /**
   * @return mixed|\Twig_Profiler_Profile
   */
  private function getProfile() {
    if (NULL === $this->profile) {
      $this->profile = unserialize($this->data['twig']);
    }

    return $this->profile;
  }

  /**
   * @param $index
   *
   * @return mixed
   */
  private function getComputedData($index) {
    if (NULL === $this->computed) {
      $this->computed = $this->computeData($this->getProfile());
    }

    return $this->computed[$index];
  }

  /**
   * @param \Twig_Profiler_Profile $profile
   *
   * @return array
   */
  private function computeData(\Twig_Profiler_Profile $profile) {
    $data = [
      'template_count' => 0,
      'block_count' => 0,
      'macro_count' => 0,
    ];

    $templates = [];
    foreach ($profile as $p) {
      $d = $this->computeData($p);

      $data['template_count'] += ($p->isTemplate() ? 1 : 0) + $d['template_count'];
      $data['block_count'] += ($p->isBlock() ? 1 : 0) + $d['block_count'];
      $data['macro_count'] += ($p->isMacro() ? 1 : 0) + $d['macro_count'];

      if ($p->isTemplate()) {
        if (!isset($templates[$p->getTemplate()])) {
          $templates[$p->getTemplate()] = 1;
        }
        else {
          $templates[$p->getTemplate()]++;
        }
      }

      foreach ($d['templates'] as $template => $count) {
        if (!isset($templates[$template])) {
          $templates[$template] = $count;
        }
        else {
          $templates[$template] += $count;
        }
      }
    }
    $data['templates'] = $templates;

    return $data;
  }
}
