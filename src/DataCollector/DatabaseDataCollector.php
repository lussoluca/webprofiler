<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\PhpSqlParser\WebprofilerPhpSqlParser;
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
    usort($queries, array("Drupal\\webprofiler\\DataCollector\\DatabaseDataCollector", "orderQuery"));

    foreach ($queries as &$query) {
      // remove caller
      unset($query['caller']['args']);
    }

    $this->data['queries'] = $queries;

    $options = $this->database->getConnectionOptions();

    // remove password field for security
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
  public function getPanel() {
    $build = array();

    $build['filters'] = \Drupal::formBuilder()->getForm('Drupal\\webprofiler\\Form\\QueryFilterForm');

    $build['container'] = array(
      '#type' => 'container',
      '#attributes' => array('id' => array('wp-query-wrapper')),
    );

    // init PhpSqlParser object
    $webprofile_phpsqlparser = new WebprofilerPhpSqlParser();

    $position = 0;
    foreach ($this->getQueries() as $query) {
      $table = $this->getTable('Query arguments', $query['args'], array('Placeholder', 'Value'));

      $explain = TRUE;
      $type = 'select';

      if (strpos($query['query'], 'UPDATE') !== FALSE) {
        $explain = FALSE;
        $type = 'update';
      }

      if (strpos($query['query'], 'INSERT') !== FALSE) {
        $explain = FALSE;
        $type = 'insert';
      }

      if (strpos($query['query'], 'DELETE') !== FALSE) {
        $explain = FALSE;
        $type = 'delete';
      }

      $profile = \Drupal::request()->get('profile');
      $copyUrl = \Drupal::urlGenerator()
        ->generate('webprofiler.database.arguments', array('profile' => $profile->getToken(), 'qid' => $position));
      $query['copy_link'] = l($this->t('Copy'), $copyUrl, array(
        'attributes' => array(
          'class' => array('use-ajax', 'wp-button', 'wp-query-copy-button'),
          'data-accepts' => 'application/vnd.drupal-modal',
          'data-dialog-options' => Json::encode(array(
            'width' => 700,
          )),
        )
      ));

      $build['container'][] = array(
        '#theme' => 'webprofiler_db_panel',
        '#query' => $query,
        '#table' => $table,
        '#explain' => $explain,
        '#query_type' => $type,
        '#position' => $position,
        '#attached' => array(
          'library' => array(
            'webprofiler/database',
            'webprofiler/highlight',
          ),
        ),
      );

      // insert into PhpSqlParser
      $webprofile_phpsqlparser->add_query($query['query']);

      $position++;
    }

    // Type of data sqlparser (label=>key_in_array)
    $tables_data_sqlparser = array(
      'Type sql' => 'type_sql_allowed',
      'Operator in Where' => 'where_type_operator'
    );

    // fields
    $build['phpsqlparser'] = array(
      '#type' => 'container',
      '#prefix' => '<h2>SQL Parser</h2>',
      '#attributes' => array('id' => 'container-phpsqlparser'),
    );

    // data for js and build table
    $setting_js_data = array();
    $setting_js_graphic = array();
    foreach ($tables_data_sqlparser as $label => $name) {

      // recovery data
      $data = $webprofile_phpsqlparser->get_data_in_table($name); // return array_table structure

      if (!empty($data)) {
        $build['phpsqlparser'][$name] = array(
          '#theme' => 'table',
          '#rows' => $data['#rows'],
          '#header' => $data['#header'],
          '#prefix' => '<h2>' . t($label) . '</h2>',
          '#suffix' => '<div id="graphic-' . $name . '" class="graphic"></div>',
          '#attributes' => $data['#attributes'],
        );

        $setting_js_data[] = $this->getAttachedJs($data['#rows']);
        $setting_js_graphic[] = $name;
      }
    }
    // /cycle

    $build['phpsqlparser']['#attached'] = array(
      'js' => array(
        array(
          'data' => array(
            'webprofiler' => array(
              'sqlparser' => array(
                'data' => $setting_js_data,
                'graphic' => $setting_js_graphic
              )
            )
          ),
          'type' => 'setting'
        ),
      ),
      'library' => array(
        'webprofiler/donut3d',
      ),
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  private function getAttachedJs($data) {
    $data_array = array();
    foreach ($data as $key => $value) {
      $data_array[$key]['label'] = $value[0]['data'];
      $data_array[$key]['value'] = $value[1]['data'];

      // generator/set color
      //$data_array[$key]['color'] = "#DC3912";
      $rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
      $color = '#' . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)] . $rand[rand(0, 15)];
      $data_array[$key]['color'] = $color;
    }

    return $data_array;
  }
}
