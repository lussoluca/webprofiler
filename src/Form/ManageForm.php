<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\PurgeForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webprofiler\Profiler\ProfilerStorageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ManageForm
 */
class ManageForm extends FormBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * @var \Drupal\webprofiler\Profiler\ProfilerStorageManager
   */
  private $profilerDownloadManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('profiler.storage_manager')
    );
  }

  /**
   * @param Profiler $profiler
   * @param \Drupal\webprofiler\Profiler\ProfilerStorageManager $profilerDownloadManager
   */
  public function __construct(Profiler $profiler, ProfilerStorageManager $profilerDownloadManager) {
    $this->profiler = $profiler;
    $this->profilerDownloadManager = $profilerDownloadManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_purge';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->profiler->disable();

    $storageId = $this->config('webprofiler.config')->get('storage');
    $storage = $this->profilerDownloadManager->getStorage($storageId);

    $form['purge'] = [
      '#type' => 'details',
      '#title' => $this->t('Purge profiles'),
      '#open' => TRUE,
    ];

    $form['purge']['purge'] = [
      '#type' => 'submit',
      '#value' => $this->t('Purge'),
      '#submit' => [[$this, 'purge']],
    ];

    $form['purge']['purge-help'] = [
      '#type' => 'inline_template',
      '#template' => '<div class="form-item">{{ message }}</div>',
      '#context' => [
        'message' => $this->t('Purge %storage profiles.', ['%storage' => $storage['title']]),
      ],
    ];

    return $form;
  }

  /**
   * Purges profiles.
   */
  public function purge(array &$form, FormStateInterface $form_state) {
    $this->profiler->purge();
    drupal_set_message($this->t('Profiles purged'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
