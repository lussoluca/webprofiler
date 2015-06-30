<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class DatabaseDataCollector
 */
class DatabaseDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $queries = $this->database->getLogger()->get('webprofiler');
    usort($queries, array(
      "Drupal\\webprofiler\\DataCollector\\DatabaseDataCollector",
      "orderQuery",
    ));

    foreach ($queries as &$query) {
      // Remove caller.
      unset($query['caller']['args']);
    }

    $this->data['queries'] = $queries;

    $options = $this->database->getConnectionOptions();

    // Remove password field for security.
    unset($options['password']);

    $this->data['database'] = $options;
  }

  /**
   * @param $a
   * @param $b
   *
   * @return int
   */
  private function orderQuery($a, $b) {
    $at = $a['time'];
    $bt = $b['time'];

    if ($at == $bt) {
      return 0;
    }
    return ($at < $bt) ? 1 : -1;
  }

  /**
   * @return array
   */
  public function getDatabase() {
    return $this->data['database'];
  }

  /**
   * @return int
   */
  public function getQueryCount() {
    return count($this->data['queries']);
  }

  /**
   * @return array
   */
  public function getQueries() {
    return $this->data['queries'];
  }

  /**
   * @return float
   */
  public function getTime() {
    $time = 0;

    foreach ($this->data['queries'] as $query) {
      $time += $query['time'];
    }

    return $time;
  }

  /**
   * @return string
   */
  public function getColorCode() {
    if ($this->getQueryCount() < 100) {
      return 'green';
    }
    if ($this->getQueryCount() < 200) {
      return 'yellow';
    }

    return 'red';
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'database';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Database');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Executed queries: @count', array('@count' => $this->getQueryCount()));
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return array(
      'webprofiler/database',
    );
  }

  /**
   * {@inheritdoc}
   */
//  public function getPanel() {
//    $build = array();
//
//    $build['filters'] = \Drupal::formBuilder()
//      ->getForm('Drupal\\webprofiler\\Form\\DatabaseFilterForm');
//
//    $build['container'] = array(
//      '#type' => 'container',
//      '#attributes' => array('id' => array('wp-query-wrapper')),
//    );
//
//    $position = 0;
//    foreach ($this->getQueries() as $query) {
//      $table = $this->getTable('Query arguments', $query['args'], array(
//          'Placeholder',
//          'Value',
//        ));
//
//      $explain = TRUE;
//      $type = 'select';
//
//      if (strpos($query['query'], 'UPDATE') !== FALSE) {
//        $explain = FALSE;
//        $type = 'update';
//      }
//
//      if (strpos($query['query'], 'INSERT') !== FALSE) {
//        $explain = FALSE;
//        $type = 'insert';
//      }
//
//      if (strpos($query['query'], 'DELETE') !== FALSE) {
//        $explain = FALSE;
//        $type = 'delete';
//      }
//
//      $profile = \Drupal::request()->get('profile');
//      $query['copy_link'] = \Drupal::linkGenerator()
//        ->generate($this->t('Copy'), new Url('webprofiler.database.arguments', array(
//          'profile' => $profile->getToken(),
//          'qid' => $position,
//        ), array(
//          'attributes' => array(
//            'class' => array('use-ajax', 'wp-button', 'wp-query-copy-button'),
//            'data-accepts' => 'application/vnd.drupal-modal',
//            'data-dialog-options' => Json::encode(array(
//              'width' => 700,
//            )),
//          ),
//        )));
//
//      $build['container'][] = array(
//        '#theme' => 'webprofiler_db_panel',
//        '#query' => $query,
//        '#table' => $table,
//        '#explain' => $explain,
//        '#query_type' => $type,
//        '#position' => $position,
//        '#attached' => array(
//          'library' => array(
//            'webprofiler/database',
//            'webprofiler/highlight',
//          ),
//        ),
//      );
//
//      $position++;
//    }
//
//    return $build;
//  }


}
