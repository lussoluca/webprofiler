<?php

namespace Drupal\webprofiler\Twig\Extension;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class ProfilerExtension
 */
class ProfilerExtension extends \Twig_Extension_Profiler {
  private $stopwatch;
  private $events;

  public function __construct(\Twig_Profiler_Profile $profile, Stopwatch $stopwatch = NULL) {
    parent::__construct($profile);

    $this->stopwatch = $stopwatch;
    $this->events = new \SplObjectStorage();
  }

  public function enter(\Twig_Profiler_Profile $profile) {
    if ($this->stopwatch && $profile->isTemplate()) {
      $this->events[$profile] = $this->stopwatch->start($profile->getName(), 'template');
    }

    parent::enter($profile);
  }

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
