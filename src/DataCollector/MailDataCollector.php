<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class MailDataCollector
 */
class MailDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var
   */
  private $messages;

  /**
   *
   */
  public function __construct() {
    $this->messages = [];
  }

  /**
   * Collects data for the given Request and Response.
   *
   * @param Request $request A Request instance
   * @param Response $response A Response instance
   * @param \Exception $exception An Exception instance
   *
   * @api
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['mail'] = $this->messages;
  }

  /**
   * @param $message
   * @param $plugin_id
   * @param $configuration
   * @param \Drupal\Core\Mail\MailInterface $mail
   */
  public function addMessage($message, $plugin_id, $configuration, MailInterface $mail) {
    $class = get_class($mail);
    $method = new \ReflectionMethod($class, 'mail');

    $this->messages[] = [
      'message' => $message,
      'plugin' => [
        'id' => $plugin_id,
        'class' => $class,
        'method' => 'mail',
        'file' => $method->getFilename(),
        'line' => $method->getStartLine(),
        'configuration' => $configuration,
      ]
    ];
  }

  /**
   * @return int
   */
  public function getMailSent() {
    return count($this->data['mail']);
  }

  /**
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'mail';
  }

  /**
   * Returns the datacollector title.
   *
   * @return string
   *   The datacollector title.
   */
  public function getTitle() {
    return $this->t('Mail');
  }
}
