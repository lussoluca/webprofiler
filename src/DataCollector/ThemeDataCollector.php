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
   * @return array
   */
  public function getData() {
    $data = $this->data;



    return $data;
  }
}
