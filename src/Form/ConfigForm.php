<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\ConfigForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 *
 */
class ConfigForm extends ConfigFormBase {

  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('profiler')
    );
  }

  /**
   * @param ConfigFactoryInterface $config_factory
   * @param Profiler $profiler
   */
  public function __construct(ConfigFactoryInterface $config_factory, Profiler $profiler) {
    parent::__construct($config_factory);

    $this->profiler = $profiler;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'webprofiler_config';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, array &$form_state) {
    $this->profiler->disable();
    $config = $this->config('webprofiler.config');

    $form['purge_on_cache_clear'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Purge on cache clear'),
      '#description' => $this->t('Deletes all profiler files during cache clear.'),
      '#default_value' => $config->get('purge_on_cache_clear'),
    );

    $form['storage'] = array(
      '#type' => 'select',
      '#title' => $this->t('Storage backend'),
      '#description' => $this->t('Choose were to store profiler data.'),
      '#options' => array(
        'profiler.file_storage' => $this->t('File'),
        'profiler.database_storage' => $this->t('Database')
      ),
      '#default_value' => $config->get('storage'),
    );

    $form['exclude'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Exclude'),
      '#default_value' => $config->get('exclude'),
      '#description' => $this->t('Path to exclude for profiling. One path per line.')
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('webprofiler.config')
      ->set('purge_on_cache_clear', $form_state['values']['purge_on_cache_clear'])
      ->set('storage', $form_state['values']['storage'])
      ->set('exclude', $form_state['values']['exclude'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
