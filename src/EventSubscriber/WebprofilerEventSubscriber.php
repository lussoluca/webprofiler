<?php

namespace Drupal\webprofiler\EventSubscriber;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Twig_Environment;
use Drupal\Core\Database\Database;

class WebprofilerEventSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @param \Drupal\Core\Session\AccountInterface
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   */
  public function __construct(AccountInterface $currentUser, UrlGeneratorInterface $urlGenerator) {
    $this->currentUser = $currentUser;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();

    if ($response->headers->has('X-Debug-Token') && null !== $this->urlGenerator) {
      $response->headers->set(
        'X-Debug-Token-Link',
        $this->urlGenerator->generate('webprofiler.profiler', array('profile' => $response->headers->get('X-Debug-Token')))
      );
    }

    // do not capture redirects or modify XML HTTP Requests
    if ($request->isXmlHttpRequest()) {
      return;
    }

    if ($this->currentUser->hasPermission('view webprofiler toolbar')) {
      $this->injectToolbar($response);
    }
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Response $response
   */
  protected function injectToolbar(Response $response) {
    $content = $response->getContent();
    $pos = mb_strripos($content, '</body>');

    if (FALSE !== $pos) {
      if ($token = $response->headers->get('X-Debug-Token')) {
        $toolbar = array(
          '#theme' => 'webprofiler_loader',
          '#token' => $token,
          '#profiler_url' => $this->urlGenerator->generate('webprofiler.toolbar', array('profile' => $token)),
        );

        $content = mb_substr($content, 0, $pos) . render($toolbar) . mb_substr($content, $pos);
        $response->setContent($content);
      }
    }
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return array(
      KernelEvents::RESPONSE => array('onKernelResponse', -128),
    );
  }
}
