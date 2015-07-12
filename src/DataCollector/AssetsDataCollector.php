<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\AssetsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\NestedArray;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about the used assets (CSS/JS).
 */
class AssetsDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * Constructs a AssetDataCollector object.
   */
  public function __construct() {
    $this->data['js'] = [];
    $this->data['css'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * @param $jsAsset
   */
  public function addJsAsset($jsAsset) {
    $this->data['js'] = NestedArray::mergeDeepArray([
      $jsAsset,
      $this->data['js']
    ]);
  }

  /**
   * @param $cssAsset
   */
  public function addCssAsset($cssAsset) {
    $this->data['css'] = NestedArray::mergeDeepArray([
      $cssAsset,
      $this->data['css']
    ]);
  }

  /**
   * Twig callback to return the amount of CSS files.
   */
  public function getCssCount() {
    return count($this->data['css']);
  }

  /**
   * Twig callback to return the amount of JS files.
   */
  public function getJsCount() {
    return count($this->data['js']) - 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'assets';
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
    return $this->t('Total assets: @count', ['@count' => ($this->getCssCount() + $this->getJsCount())]);
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    $build = array();
//
//    $build['css_title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>CSS</h3>',
//    );
//
//    $cssHeader = array(
//      'file',
//      'preprocess',
//      'type',
//      'version',
//      'media',
//      'every_page',
//      'preprocess',
//    );
//
//    $rows = array();
//    foreach ($this->getCssFiles() as $css) {
//      $row = array();
//
//      $row[] = $css['data'];
//      $row[] = ($css['preprocess']) ? $this->t('true') : $this->t('false');
//      $row[] = $css['type'];
//      $row[] = isset($css['version']) ? $css['version'] : '-';
//      $row[] = $css['media'];
//      $row[] = ($css['every_page']) ? $this->t('true') : $this->t('false');
//      $row[] = ($css['preprocess']) ? $this->t('true') : $this->t('false');
//
//      $rows[] = $row;
//    }
//
//    $build['css_table'] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => $cssHeader,
//      '#sticky' => TRUE,
//    );
//
//    $build['js_title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>JS</h3>',
//    );
//
//    $jsHeader = array(
//      'file',
//      'preprocess',
//      'type',
//      'version',
//      'scope',
//      'minified',
//      'every_page',
//      'preprocess',
//    );
//
//    $rows = array();
//    foreach ($this->getJsFiles() as $js) {
//      $row = array();
//
//      $row[] = $js['data'];
//      $row[] = ($js['preprocess']) ? $this->t('true') : $this->t('false');
//      $row[] = $js['type'];
//      $row[] = isset($js['version']) ? $js['version'] : '-';
//      $row[] = $js['scope'];
//      $row[] = ($js['minified']) ? $this->t('true') : $this->t('false');
//      $row[] = ($js['every_page']) ? $this->t('true') : $this->t('false');
//      $row[] = ($js['preprocess']) ? $this->t('true') : $this->t('false');
//
//      $rows[] = $row;
//    }
//
//    $build['js_table'] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => $jsHeader,
//      '#sticky' => TRUE,
//    );
//
//    // Js settings.
//    if (isset($this->data['js']['drupalSettings'])) {
//      $build['js-settings'] = array(
//        array(
//          '#type' => 'inline_template',
//          '#template' => '<h3>{{ message }}</h3>',
//          '#context' => array(
//            'message' => $this->t('JS settings'),
//          ),
//          array(
//            '#type' => 'inline_template',
//            '#template' => '<textarea style="width:100%; height:400px">{{ settings }}</textarea>',
//            '#context' => array(
//              'settings' => json_encode($this->data['js']['drupalSettings']['data'], JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
//            ),
//          ),
//        ),
//      );
//    }
//
//    return $build;
//  }

}
