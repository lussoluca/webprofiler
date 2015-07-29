<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class DrupalDataCollector
 */
class DrupalDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  private $redirectDestination;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  private $urlGenerator;

  /**
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirectDestination
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   */
  public function __construct(RedirectDestinationInterface $redirectDestination, UrlGeneratorInterface $urlGenerator) {
    $this->redirectDestination = $redirectDestination;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['version'] = Drupal::VERSION;
    $this->data['profile'] = drupal_get_profile();
    $this->data['config_url'] = $this->urlGenerator->generateFromRoute('webprofiler.admin_configure', [], ['query' => $this->redirectDestination->getAsArray()]);
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->data['version'];
  }

  /**
   * @return string
   */
  public function getProfile() {
    return $this->data['profile'];
  }

  /**
   * @return string
   */
  public function getConfigUrl() {
    return $this->data['config_url'];
  }

  /**
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'drupal';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Drupal');
  }

  /**
   * {@inheritdoc}
   */
  public function hasPanel() {
    return FALSE;
  }
}
