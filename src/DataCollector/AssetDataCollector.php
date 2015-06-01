<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\AssetDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolver;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about the used assets (CSS/JS).
 */
class AssetDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * The javascript asset collection.
   *
   * @var AssetCollectionRendererInterface
   */
  private $jsCollectionRenderer;

  /**
   * The css asset collection.
   *
   * @var AssetCollectionRendererInterface
   */
  private $cssCollectionRenderer;

  /**
   * Constructs a AssetDataCollector object.
   *
   * @param AssetCollectionRendererInterface $jsCollectionRenderer
   *   The javascript asset collection renderer.
   * @param AssetCollectionRendererInterface $cssCollectionRenderer
   *   The css asset collection renderer.
   */
  public function __construct(AssetCollectionRendererInterface $jsCollectionRenderer, AssetCollectionRendererInterface $cssCollectionRenderer) {
    $this->jsCollectionRenderer = $jsCollectionRenderer;
    $this->cssCollectionRenderer = $cssCollectionRenderer;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['js'] = array();
    $this->data['css'] = array();

    if(is_array($this->jsCollectionRenderer->getAssets())) {
      $this->data['js'] = NestedArray::mergeDeepArray($this->jsCollectionRenderer->getAssets());
    }

    if(is_array($this->cssCollectionRenderer->getAssets())) {
      $this->data['css'] = NestedArray::mergeDeepArray($this->cssCollectionRenderer->getAssets());
    }
  }

  /**
   * Twig callback to return the amount of CSS files.
   */
  public function getCssCount() {
    return count($this->data['css']);
  }

  /**
   * Twig callback to return the CSS files.
   */
  public function getCssFiles() {
    $result = array();
    foreach ($this->data['css'] as $option) {
      $result[] = $option;
    }
    uasort($result, array($this, 'sortByWeight'));
    return $result;
  }

  /**
   * Twig callback to return the amount of JS files.
   */
  public function getJsCount() {
    return count($this->data['js']) - 1;
  }

  /**
   * Twig callback to return the JS files.
   */
  public function getJsFiles() {
    $result = array();
    foreach ($this->data['js'] as $option) {
      if ($option['type'] != 'setting') {
        $result[] = $option;
      }
    }
    uasort($result, array($this, 'sortByWeight'));
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'asset';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Assets');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total assets: @count', array('@count' => ($this->getCssCount() + $this->getJsCount())));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $build['css_title'] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>CSS</h3>',
    );

    $cssHeader = array(
      'file',
      'preprocess',
      'type',
      'version',
      'media',
      'every_page',
      'preprocess',
    );

    $rows = array();
    foreach ($this->getCssFiles() as $css) {
      $row = array();

      $row[] = $css['data'];
      $row[] = ($css['preprocess']) ? $this->t('true') : $this->t('false');
      $row[] = $css['type'];
      $row[] = isset($css['version']) ? $css['version'] : '-';
      $row[] = $css['media'];
      $row[] = ($css['every_page']) ? $this->t('true') : $this->t('false');
      $row[] = ($css['preprocess']) ? $this->t('true') : $this->t('false');

      $rows[] = $row;
    }

    $build['css_table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $cssHeader,
      '#sticky' => TRUE,
    );

    $build['js_title'] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>JS</h3>',
    );

    $jsHeader = array(
      'file',
      'preprocess',
      'type',
      'version',
      'scope',
      'minified',
      'every_page',
      'preprocess',
    );

    $rows = array();
    foreach ($this->getJsFiles() as $js) {
      $row = array();

      $row[] = $js['data'];
      $row[] = ($js['preprocess']) ? $this->t('true') : $this->t('false');
      $row[] = $js['type'];
      $row[] = isset($js['version']) ? $js['version'] : '-';
      $row[] = $js['scope'];
      $row[] = ($js['minified']) ? $this->t('true') : $this->t('false');
      $row[] = ($js['every_page']) ? $this->t('true') : $this->t('false');
      $row[] = ($js['preprocess']) ? $this->t('true') : $this->t('false');

      $rows[] = $row;
    }

    $build['js_table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $jsHeader,
      '#sticky' => TRUE,
    );

    // Js settings.
    if (isset($this->data['js']['drupalSettings'])) {
      $build['js-settings'] = array(
        array(
          '#type' => 'inline_template',
          '#template' => '<h3>{{ message }}</h3>',
          '#context' => array(
            'message' => $this->t('JS settings'),
          ),
          array(
            '#type' => 'inline_template',
            '#template' => '<textarea style="width:100%; height:400px">{{ settings }}</textarea>',
            '#context' => array(
              'settings' => json_encode($this->data['js']['drupalSettings']['data'], JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
            ),
          ),
        ),
      );
    }

    return $build;
  }

  /**
   * Sorts an assets collection array by weight.
   *
   * @param array $a
   *   The first element.
   * @param array $b
   *   The second element.
   *
   * @return int
   *   0 if weight are equals, -1 if first is lesser that second, 1 otherwise.
   */
  private function sortByWeight(array $a, array $b) {
    if ($a['weight'] === $b['weight']) {
      return 0;
    }

    if ($a['weight'] < $b['weight']) {
      return -1;
    }
    else {
      return 1;
    }
  }

}
