<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Routing\UrlGeneratorInterface;
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
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   */
  public function __construct(TranslationInterface $translation, UrlGeneratorInterface $urlGenerator) {
    $this->translation = $translation;

    $this->data['translations']['translated'] = [];
    $this->data['translations']['untranslated'] = [];
    $this->data['user_interface_translations_path'] = $urlGenerator->generateFromRoute('locale.translate_page');
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
    return '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';
  }
}
