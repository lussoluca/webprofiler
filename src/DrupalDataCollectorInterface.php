<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DrupalDataCollectorInterface.
 */

namespace Drupal\webprofiler;

/**
 * Interface DrupalDataCollectorInterface.
 */
interface DrupalDataCollectorInterface {

  /**
   * Returns the datacollector title.
   *
   * @return string
   *   The datacollector title.
   */
  public function getTitle();

  /**
   * Returns the name of the collector.
   *
   * @return string
   *   The collector name.
   */
  public function getName();

  /**
   * Returns th string used in vertical tab summary.
   *
   * @return string
   *   The panel summary.
   */
  public function getPanelSummary();

  /**
   * Returns true if this datacollector has a detail panel.
   *
   * @return bool
   *   True if datacollector has a detail panel, false otherwise.
   */
  public function hasPanel();

  /**
   * Returns the libraries needed in detail panel.
   *
   * @return array
   *   The render array for detail panel.
   */
  public function getLibraries();

  /**
   * @return array
   */
  public function getDrupalSettings();
}
