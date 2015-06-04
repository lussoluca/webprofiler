<?php

namespace Drupal\webprofiler\Authentication;

use Drupal\Core\Authentication\AuthenticationManager;
use Drupal\Core\Authentication\AuthenticationManagerInterface;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthenticationManagerWrapper
 */
class AuthenticationManagerWrapper extends AuthenticationManager {

  /**
   * @var \Drupal\Core\Authentication\AuthenticationManager
   */
  private $authenticationManager;

  /**
   * @param \Drupal\Core\Authentication\AuthenticationManagerInterface $authenticationManager
   */
  public function __construct(AuthenticationManagerInterface $authenticationManager) {
    $this->authenticationManager = $authenticationManager;
  }

  /**
   * {@inheritdoc}
   */
  public function addProvider(AuthenticationProviderInterface $provider, $provider_id, $priority = 0, $global = FALSE) {
    $this->authenticationManager->addProvider($provider, $provider_id, $priority, $global);
  }

  /**
   * {@inheritdoc}
   */
  public function challengeException(Request $request, \Exception $previous) {
    return $this->authenticationManager->challengeException($request, $previous);
  }

  /**
   * {@inheritdoc}
   */
  public function appliesToRoutedRequest(Request $request, $authenticated) {
    return $this->authenticationManager->appliesToRoutedRequest($request, $authenticated);
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    return $this->authenticationManager->applies($request);
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    return $this->authenticationManager->authenticate($request);
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
