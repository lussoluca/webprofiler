<?php

namespace Drupal\webprofiler\Helper;

use Drupal\Component\Utility\SafeMarkup;

/**
 * Class ClassShortener
 */
class ClassShortener implements ClassShortenerInterface {

  /**
   * {@inheritdoc}
   */
  public function shortenClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    return SafeMarkup::format("<abbr title=\"@class\">@short</abbr>", array('@class' => $class, '@short' => $short));
  }
}
