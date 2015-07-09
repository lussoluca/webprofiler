<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Form\FormBuilderWrapper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class FormsDataCollector
 */
class FormsDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\webprofiler\Form\FormBuilderWrapper
   */
  private $formBuilder;

  /**
   * @param \Drupal\webprofiler\Form\FormBuilderWrapper $formBuilder
   */
  public function __construct(FormBuilderWrapper $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['forms'] = $this->formBuilder->getBuildForm();
  }

  /**
   * @return array
   */
  public function getForms() {
    return $this->data['forms'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'forms';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Forms');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Rendered forms: @forms', ['@forms' => count($this->data['forms'])]);
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    $build = array();
//
//    if (count($this->getForms()) == 0) {
//      $build['no-forms'] = array(
//        '#type' => 'inline_template',
//        '#template' => '{{ message }}',
//        '#context' => array(
//          'message' => $this->t('No forms.'),
//        ),
//      );
//
//      return $build;
//    }
//
//    foreach ($this->getForms() as $form_id => $form) {
//      $formData = $form['form'];
//
//      $build[$form_id]['class'] = array(
//        '#type' => 'inline_template',
//        '#template' => '<h3>#{{ id }}: {{ class }}</h3>',
//        '#context' => array(
//          'id' => $form_id,
//          'class' => $form['class'],
//        ),
//      );
//
//      $rows = array();
//      foreach ($formData as $key => $value) {
//        if (strpos($key, '#') !== 0) {
//          $row = array();
//
//          $row[] = $key;
//          $row[] = isset($value['#title']) ? $value['#title'] : '-';
//          $row[] = isset($value['#access']) ? $value['#access'] : '-';
//          $row[] = isset($value['#type']) ? $value['#type'] : '-';
//
//          $rows[] = $row;
//        }
//      }
//
//      $build[$form_id]['fields'] = array(
//        '#type' => 'table',
//        '#rows' => $rows,
//        '#header' => array(
//          $this->t('Name'),
//          $this->t('Title'),
//          $this->t('Access'),
//          $this->t('Type'),
//        ),
//        '#sticky' => TRUE,
//      );
//    }
//
//    return $build;
//  }
}
