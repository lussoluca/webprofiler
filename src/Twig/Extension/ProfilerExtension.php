<?php

namespace Drupal\webprofiler\Twig\Extension;

use Drupal\webprofiler\Helper\ClassShortenerInterface;
use Drupal\webprofiler\Helper\IdeLinkGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class ProfilerExtension
 */
class ProfilerExtension extends \Twig_Extension_Profiler {

  /**
   * @var \Symfony\Component\Stopwatch\Stopwatch
   */
  private $stopwatch;

  /**
   * @var \SplObjectStorage
   */
  private $events;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Twig_Profiler_Profile $profile, Stopwatch $stopwatch = NULL) {
    parent::__construct($profile);

    $this->stopwatch = $stopwatch;
    $this->events = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function enter(\Twig_Profiler_Profile $profile) {
    if ($this->stopwatch && $profile->isTemplate()) {
      $this->events[$profile] = $this->stopwatch->start($profile->getName(), 'template');
    }

    parent::enter($profile);
  }

  /**
   * {@inheritdoc}
   */
  public function leave(\Twig_Profiler_Profile $profile) {
    parent::leave($profile);

    if ($this->stopwatch && $profile->isTemplate()) {
      $this->events[$profile]->stop();
      unset($this->events[$profile]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'native_profiler';
  }
}
