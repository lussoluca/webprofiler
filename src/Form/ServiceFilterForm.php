<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\ServiceFilterForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ServiceFilterForm
 */
class ServiceFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_service_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['sid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Id'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('sid'),
    ];

    $form['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Class'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('class'),
    ];

    $form['tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tags'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('tags'),
    ];

    $form['initialized'] = [
      '#type' => 'select',
      '#title' => $this->t('Initialized'),
      '#options' => [
        '' => $this->t('Any'),
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#default_value' => $this->getRequest()->query->get('initialized'),
    ];

    $form['service-filter'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#prefix' => '<div id="filter-service-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['button--primary']],
    ];

    $form['#attributes'] = ['id' => ['service-filter-form']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
