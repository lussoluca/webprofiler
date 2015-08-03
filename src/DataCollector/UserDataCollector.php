<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Authentication\AuthenticationCollectorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class UserDataCollector
 */
class UserDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private $entityManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @var \Drupal\Core\Authentication\AuthenticationCollectorInterface
   */
  private $providerCollector;

  /**
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Authentication\AuthenticationCollectorInterface $providerCollector
   */
  public function __construct(AccountInterface $currentUser, EntityManagerInterface $entityManager, ConfigFactoryInterface $configFactory, AuthenticationCollectorInterface $providerCollector) {
    $this->currentUser = $currentUser;
    $this->entityManager = $entityManager;
    $this->configFactory = $configFactory;
    $this->providerCollector = $providerCollector;
  }

  /**
   * @return \Drupal\Core\Session\AccountInterface
   */
  public function name() {
    return SafeMarkup::checkPlain($this->data['name']);
  }

  /**
   * @return bool
   */
  public function authenticated() {
    return $this->data['authenticated'];
  }

  /**
   * @return array
   */
  public function roles() {
    return $this->data['roles'];
  }

  /**
   * @return string
   */
  public function provider() {
    return $this->data['provider'];
  }

  /**
   * @return string
   */
  public function anonymous() {
    return $this->data['anonymous'];
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['name'] = $this->currentUser->getUsername();
    $this->data['authenticated'] = $this->currentUser->isAuthenticated();

    $this->data['roles'] = array();
    $storage = $this->entityManager->getStorage('user_role');
    foreach ($this->currentUser->getRoles() as $role) {
      $entity = $storage->load($role);
      $this->data['roles'][] = $entity->label();
    }

    foreach ($this->providerCollector->getSortedProviders() as $provider_id => $provider) {
      if ($provider->applies($request)) {
        $this->data['provider'] = $provider_id;
      }
    }

    $this->data['anonymous'] = $this->configFactory->get('user.settings')->get('anonymous');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'user';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('User');
  }

  /**
   * {@inheritdoc}
   */
  public function hasPanel() {
    return FALSE;
  }
}
