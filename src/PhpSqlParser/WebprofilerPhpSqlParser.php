<?php

/**
 * @file
 * Contains \Drupal\webprofiler\WebprofilerPhpSqlParser.
 */
 
namespace Drupal\webprofiler\PhpSqlParser; 

/**
 *  WebProfilerPhpSqlParser
 */ 
class WebprofilerPhpSqlParser {

  /**
   * Array with sql string
   *
   * @var array $sql_array
   */
  private $sql_array;
  
  /**
   * Array with data calcolated
   *
   * @var array $data_information
   */
  private $data_information;
   
  /**
   * Array of type sql allowed or researched 
   *
   * @var array $type_sql_allowed (Default: ('SELECT','INSERT','UPDATE','DELETE','REPLACE','SET','DROP'))
   */
  private $type_sql_allowed;
  
  /**
   * Object to use for reaserched data information
   *
   * \PHPSQLParser $parser_object Object for parsed the sql array
   */
  private $parser_object;
   
  /**
   * construct
   */
  public function __construct() {
    
    // default array
    $this->sql_array = array();
    $this->data_information = array();
    
    // default sql_type research
    $this->type_sql_allowed = array(
      'SELECT',
      'INSERT',
      'UPDATE',
      'DELETE',
      'REPLACE',
      'SET',
      'DROP',
    );
    // load PHPSQLParser class
    if(!$this->phpsqlparse_require_class()){
      $this->error();
      $this->parser_object = NULL;
    }else{
      $this->parser_object = new \PHPSQLParser();
    }
    
  }
  
  /**
   * Add sql to array 
   *
   * @param string $sql_add 
   *   Add sql-text
   */
  public function add_query($sql_add){
        
    $this->sql_array[] = $this->sanitize_sql($sql_add);
    
  }
  
  /**
   * Get string query in array of position passed 
   *
   * @param int $position 
   *   Selected index of array
   * @return int Sql-text selected
   */
  public function get_query(int $position) {
    
    // exist
    if(isset($this->sql_array[$position])){
      return $this->sql_array[$position];
    }
    return NULL;
    
  }
  
  /**
   * Retrieves information
   * 
   * @param string $type 
   *   Type of information (options: all, type_sql_allowed, type_sql_all, where_type_operator) 
   * @return array Calculated date information
   */
  public function get_datainformation($type = 'all') {
    
    // Execute calcolate if not execute 
    if(empty($this->data_information) && !empty($this->sql_array)){
      $this->play();
    }
    // type not set
    if($type == 'all'){
      return $this->data_information; 
    }
    // type set
    if(isset($this->data_information[$type])){
      return array($type => $this->data_information[$type]);
    }
    return NULL;
    
  }
  
  /**
   * Get 'type_sql_allowed'
   *
   * @return array
   */
  public function get_type_sql_allowed() {
    
    return $this->type_sql_allowed;
    
  }
  
  /**
   * Set 'type_sql_allowed'
   * 
   * @param array $type_sql_allowed 
   *   Type Sql research
   */
  public function set_type_sql_allowed($type_sql_allowed) {
    
    $this->type_sql_allowed = $type_sql_allowed;
    
  }
  
  /**
   * Function to use for start recovery data information
   */
  public function play(){
    
    //$this->parser_object = new PHPSQLParser();
    if($this->parser_object == NULL){
      $this->error();
      return;
    }
    // cycle for any sql in array
    foreach ($this->sql_array as $position => $sql) {
      // save local 
      $parsed[$position] = $this->parser_object->parse($sql, true);      
    }
    // recovery 'type sql'
    $this->calcolate_type_sql($parsed);
    // recovery 'where operator'
    $this->calcolate_type_where_operator($parsed);

  }
  
  /**
   * 
   */
  private function calcolate_type_sql($parsers) {
    // statement
    $array_temp_type_sql_all = array();
    
    // cycle $parsers
    foreach ($parsers as $key => $value) {
            
      // TypeSQL All
      $parsers_key = array_keys($value);  
          
      // cycle $parsers_key
      foreach ($parsers_key as $key => $value) {
        
        if(isset($array_temp_type_sql_all[$value])){
          // increment value
          $array_temp_type_sql_all[$value] = $array_temp_type_sql_all[$value]+1;
        }else{
          // set value
          $array_temp_type_sql_all[$value] = 1;
        }
        
      }// /cycle $parsers_key
      
      // TypeSQL allowed
      $array_temp_type_sql_allowed = (
        array_intersect_key($array_temp_type_sql_all, array_flip($this->type_sql_allowed))
      );
      
    }// /cycle $parsers
    
    // save all
    $this->data_information['type_sql_all'] = $array_temp_type_sql_all;    
    $this->data_information['type_sql_allowed'] = $array_temp_type_sql_allowed;
    
  }
  
  /**
   * 
   */
  private function calcolate_type_where_operator($parsers) {
    // statement
    $array_temp_where_type_operator = array();
    
    // cycle $parsers
    foreach ($parsers as $key => $parser) {
      
      // cycle $parser
      foreach ($parser as $parser_key => $parser_value) {
           
        // Where Type
        if($parser_key == "WHERE"){
          
          // cycle $parser_value              
          foreach ($parser_value as $key => $value) {
            
            // check operator
            if($value['expr_type'] == 'operator'){
              // new or exist 
              if(isset($array_temp_where_type_operator[$value['base_expr']])){
                $array_temp_where_type_operator[$value['base_expr']] = $array_temp_where_type_operator[$value['base_expr']]+1;
              }else{
                $array_temp_where_type_operator[$value['base_expr']] = 1;
              }              
            }
            
          }// /cycle $parser_value 
          
        }//end IF WHERE Type                
      }// /cycle $parser           
    }// /cycle $parsers
    
    // save
    $this->data_information['where_type_operator'] = $array_temp_where_type_operator;    
    
  }  
  
  /**
   * Sanitize sql (:db => db)
   * 
   * @param string $sql 
   *   sql string
   * @return string sql sanitezed
   */
  private function sanitize_sql($sql) {
    
    $sql_sanitize = str_replace(':db','db', $sql);
    return $sql_sanitize;
    
  }
  
  /**
   * Return data in array tables
   * @param string $type 
   *   Type of information (options: all, type_sql_allowed, type_sql_all, where_type_operator) 
   * @return string markup of table data
   */
  public function get_data_in_table($type = 'all') {
    // get data information
    $data = $this->get_datainformation($type);
    
    if(empty($data)){
      return array();
    }

    // default tables array
    $tables = array();
    // cycle $data
    foreach ($data as $key => $table_data) {
      
      $header = array(
        array(
          'data' => t('Operator'),
        ),
        array(
          'data' => t('Number'),
        ),
      );
      
      $rows = array();      
      foreach ($table_data as $key_table_data => $value_table_data) {
        $rows[] = array(
          array('data' => $key_table_data),
          array('data' => $value_table_data),
        );
      }
      
      $table = array(
        '#theme' => 'table', 
        '#header' => $header, 
        '#rows' => $rows, 
        '#attributes' => array(
          'id' => 'table-'.$type,
          'class' => 'table table-phpsqlparser'
         ),
      );
      
      $tables[] = $table;
    }// /cycle $data    
    
    if(count($tables) == 1){
      return array_shift($tables);
    }
    
    return $tables;
    
  }
  
  /**
   * Include the library PHP PHPSQLParser.
   * 
   * @return boolean if class is include or not
   */
  private function phpsqlparse_require_class() {
    
    $path = DRUPAL_ROOT . '/libraries/php-sql-parser/PHPSQLParser.php';
    if(!@include($path)){
      return FALSE;
    } 
    return TRUE;
    
  }
  
  /**
   * Error function stamp
   * 
   * @param string $message 
   *   Error message.
   */
  private function error($message = null){
    
    if($message == null ) {
      
      $message = t(
        'Class PHPSQLParser not found! Download library PHPSQLParser @link and copy PHP-SQL-Parser/src/* into root/libraries/php-sql-parser.', 
        array('@link' => l('(download)', "https://code.google.com/p/php-sql-parser/"))
      );
      
    }
    drupal_set_message($message, 'error', $repeat = TRUE);
    watchdog('webprofile', $message, $variables = array(), $severity = WATCHDOG_ERROR, $link = NULL);   
    
  }
  
  /**
   * Print data in json.
   * 
   * @param unknown $data 
   *   Data.
   * @return unknown 
   */
  public function get_injson($data = NULL) {
    
    if($data == NULL){
      return json_encode($data);
    }
    return json_encode($this->get_datainformation());
    
  }
  
}// end class 'WebprofilerPhpSqlParser'
