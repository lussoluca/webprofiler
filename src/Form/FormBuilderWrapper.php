<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\FormBuilderWrapper.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormCacheInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormSubmitterInterface;
use Drupal\Core\Form\FormValidatorInterface;
use Drupal\webprofiler\DataCollector\FormDataCollector;

/**
 * Class FormBuilderWrapper
 */
class FormBuilderWrapper implements FormBuilderInterface, FormValidatorInterface, FormSubmitterInterface, FormCacheInterface {

  /**
   * @var \Drupal\webprofiler\DataCollector\FormDataCollector
   */
  private $dataCollector;

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  private $formBuilder;

  /**
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   * @param \Drupal\webprofiler\DataCollector\FormDataCollector $dataCollector
   */
  public function __construct(FormBuilderInterface $formBuilder, FormDataCollector $dataCollector) {
    $this->formBuilder = $formBuilder;
    $this->dataCollector = $dataCollector;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareForm($form_id, &$form, FormStateInterface &$form_state) {
    $this->formBuilder->prepareForm($form_id, $form, $form_state);

    $elements = array();
    foreach ($form as $key => $value) {
      if (strpos($key, '#') !== 0) {
        $elements[$key]['#title'] = isset($value['#title']) ? $value['#title'] : NULL;
        $elements[$key]['#access'] = isset($value['#access']) ? $value['#access'] : NULL;
        $elements[$key]['#type'] = isset($value['#type']) ? $value['#type'] : NULL;
      }
    }

    $buildInfo = $form_state->getBuildInfo();

    $this->dataCollector->addForm($buildInfo['form_id'], [
      'class' => get_class($buildInfo['callback_object']),
      'form' => $elements,
    ]);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId($form_arg, FormStateInterface &$form_state) {
    return $this->formBuilder->getFormId($form_arg, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getForm($form_arg) {
    return $this->formBuilder->getForm($form_arg);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm($form_id, FormStateInterface &$form_state) {
    return $this->formBuilder->buildForm($form_id, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function rebuildForm($form_id, FormStateInterface &$form_state, $old_form = NULL) {
    return $this->formBuilder->rebuildForm($form_id, $form_state, $old_form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm($form_arg, FormStateInterface &$form_state) {
    $this->formBuilder->submitForm($form_arg, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveForm($form_id, FormStateInterface &$form_state) {
    return $this->formBuilder->retrieveForm($form_id, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function processForm($form_id, &$form, FormStateInterface &$form_state) {
    $this->formBuilder->processForm($form_id, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function doBuildForm($form_id, &$element, FormStateInterface &$form_state) {
    return $this->formBuilder->doBuildForm($form_id, $element, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getCache($form_build_id, FormStateInterface $form_state) {
    $this->formBuilder->getCache($form_build_id, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function setCache($form_build_id, $form, FormStateInterface $form_state) {
    $this->formBuilder->setCache($form_build_id, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteCache($form_build_id) {
    $this->formBuilder->deleteCache($form_build_id);
  }

  /**
   * {@inheritdoc}
   */
  public function doSubmitForm(&$form, FormStateInterface &$form_state) {
    return $this->formBuilder->doSubmitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function executeSubmitHandlers(&$form, FormStateInterface &$form_state) {
    $this->formBuilder->executeSubmitHandlers($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function redirectForm(FormStateInterface $form_state) {
    return $this->formBuilder->redirectForm($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function executeValidateHandlers(&$form, FormStateInterface &$form_state) {
    $this->formBuilder->executeValidateHandlers($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm($form_id, &$form, FormStateInterface &$form_state) {
    $this->formBuilder->validateForm($form_id, $form, $form_state);
  }
}
