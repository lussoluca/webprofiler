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

/**
 * Class ThemeDataCollector
 */
class ThemeDataCollector extends DataCollector implements DrupalDataCollectorInterface {

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
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   * @param \Drupal\Core\Theme\ThemeNegotiatorInterface $themeNegotiator
   */
  public function __construct(ThemeManagerInterface $themeManager, ThemeNegotiatorInterface $themeNegotiator) {
    $this->themeManager = $themeManager;
    $this->themeNegotiator = $themeNegotiator;
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
      'extensions' => $activeTheme->getExtension(),
      'styleSheetsRemove' => $activeTheme->getStyleSheetsRemove(),
      'libraries' => $activeTheme->getLibraries(),
      'regions' => $activeTheme->getRegions(),
    ];

    if($this->themeNegotiator instanceof ThemeNegotiatorWrapper) {
      $this->data['negotiator'] = [
        'class' => $this->getMethodData($this->themeNegotiator->getNegotiator(), 'determineActiveTheme'),
        'id' => $this->themeNegotiator->getNegotiator()->_serviceId,
        ];
    }
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
}
