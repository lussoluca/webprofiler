<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\DatabaseFilterForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class DatabaseFilterForm
 */
class DatabaseFilterForm extends FormBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler')
    );
  }

  /**
   * @param Profiler $profiler
   */
  public function __construct(Profiler $profiler) {
    $this->profiler = $profiler;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_query_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $types = [
      '' => $this->t('Any'),
      'select' => 'SELECT',
      'update' => 'UPDATE',
      'insert' => 'INSERT',
      'delete' => 'DELETE',
    ];

    $form['query-type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $types,
      '#default_value' => $this->getRequest()->query->get('query-type'),
    ];

    $profile = $this->getRequest()->attributes->get('profile');

    /** @var DatabaseDataCollector $databaseCollector */
    $databaseCollector = $profile->getCollector('database');

    $queries = $databaseCollector->getQueries();

    $callers = ['' => $this->t('Any')];
    foreach ($queries as $query) {
      if ($query['caller']['class']) {
        $class = str_replace('\\', '_', $query['caller']['class']);
        $callers[$class] = $query['caller']['class'];
      }
    }

    $form['query-caller'] = [
      '#type' => 'select',
      '#title' => $this->t('Caller'),
      '#options' => $callers,
    ];

    $form['query-filter'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#prefix' => '<div id="filter-query-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['button--primary']],
    ];

    $form['#attributes'] = ['id' => ['database-filter-form']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
