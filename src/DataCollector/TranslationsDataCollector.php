<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\StringTranslation\TranslationManagerWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class TranslationsDataCollector
 */
class TranslationsDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  private $translation;

  /**
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   */
  public function __construct(TranslationInterface $translation) {
    $this->translation = $translation;

    $this->data['translations']['translated'] = [];
    $this->data['translations']['untranslated'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    if($this->translation instanceof TranslationManagerWrapper) {
      /** \Drupal\webprofiler\StringTranslation\TranslationManagerWrapper $this->translation */
      $this->data['translations']['translated'] = $this->translation->getTranslated();
      $this->data['translations']['untranslated'] = $this->translation->getUntranslated();
    }
  }

  /**
   * @return int
   */
  public function getTranslatedCount() {
    return count($this->data['translations']['translated']);
  }

  /**
   * @return int
   */
  public function getUntranslatedCount() {
    return count($this->data['translations']['untranslated']);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'translations';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Translations');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Translated: @translated, untranslated: @untranslated', [
      '@translated' => $this->getTranslatedCount(),
      '@untranslated' => $this->getUntranslatedCount()
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon() {
    return 'iVBORw0KGgoAAAANSUhEUgAAABUAAAAcCAYAAACOGPReAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAbElEQVRIx2NgGAXUBowMDAwMaWlp/6ll4KxZsxhZYJy0tDRqGMjAwMDAwEQL77OgCxSXlJBsSG9PDwqfJi6lj/fRvTJ4XYocUTBXE4q8oRtRRBnKwsw8RFw6fA0lKkd1dnYOIpfCCthRMIIAAI0IFu9Hxh7ZAAAAAElFTkSuQmCC';
  }
}
