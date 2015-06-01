<?php

namespace Drupal\webprofiler\Authentication;

use Drupal\Core\Authentication\AuthenticationManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthenticationManagerWrapper
 */
class AuthenticationManagerWrapper extends AuthenticationManager {

  public function getProvider(Request $request) {
    foreach ($this->getSortedProviders() as $provider_id => $provider) {
      if ($provider->applies($request)) {
        return $provider_id;
      }
    }
  }
}
