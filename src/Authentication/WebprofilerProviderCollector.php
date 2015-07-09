<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Authentication\WebprofilerProviderCollector.
 */

namespace Drupal\webprofiler\Authentication;

use Drupal\Core\Authentication\AuthenticationManagerInterface;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WebprofilerProviderCollector
 */
class WebprofilerProviderCollector implements AuthenticationManagerInterface {

  /**
   * Array of all providers and their priority.
   *
   * @var array
   */
  protected $providerOrders = [];

  /**
   * Sorted list of registered providers.
   *
   * @var \Drupal\Core\Authentication\AuthenticationProviderInterface[]
   */
  protected $sortedProviders;

  /**
   * {@inheritdoc}
   */
  public function addProvider(AuthenticationProviderInterface $provider, $provider_id, $priority = 0, $global = FALSE) {
    $this->providerOrders[$priority][$provider_id] = $provider;
    // Force the builders to be re-sorted.
    $this->sortedProviders = NULL;
  }

  /**
   * Returns the sorted array of authentication providers.
   *
   * @todo Replace with a list of providers sorted during compile time in
   *   https://www.drupal.org/node/2432585.
   *
   * @return \Drupal\Core\Authentication\AuthenticationProviderInterface[]
   *   An array of authentication provider objects.
   */
  protected function getSortedProviders() {
    if (!isset($this->sortedProviders)) {
      // Sort the builders according to priority.
      krsort($this->providerOrders);
      // Merge nested providers from $this->providers into $this->sortedProviders.
      $this->sortedProviders = [];
      foreach ($this->providerOrders as $providers) {
        $this->sortedProviders = array_merge($this->sortedProviders, $providers);
      }
    }
    return $this->sortedProviders;
  }

  /**
   * Returns the id of the authentication provider for a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   *
   * @return string|NULL
   *   The id of the first authentication provider which applies to the request.
   *   If no application detects appropriate credentials, then NULL is returned.
   */
  public function getProvider(Request $request) {
    foreach ($this->getSortedProviders() as $provider_id => $provider) {
      if ($provider->applies($request)) {
        return $provider_id;
      }
    }
  }
}
