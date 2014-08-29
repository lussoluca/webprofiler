<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\ViewsDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Entity\Block\BlockStorageDecorator;
use Drupal\webprofiler\Entity\EntityManagerWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about rendered views.
 */
class BlockDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /** @var $entityManager */
  private $entityManager;

  /**
   * @param EntityManagerWrapper $entityManager
   */
  public function __construct(EntityManagerWrapper $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $loaded = $this->entityManager->getLoaded();
    $rendered = $this->entityManager->getRendered();

    $this->data['blocks'] = array();

    if ($loaded) {
      foreach ($loaded as $blocks) {
        /** @var BlockStorageDecorator $blocks */
        foreach ($blocks->getBlocks() as $block) {
          $this->data['blocks']['loaded'][] = array(
            'id' => $block->id,
            'region' => $block->get('region'),
            'status' => $block->get('status'),
            'theme' => $block->get('theme'),
            'plugin' => $block->get('plugin'),
            'settings' => $block->get('settings'),
          );
        }
      }
    }

    if ($rendered) {
      foreach ($rendered as $blocks) {
        /** @var BlockStorageDecorator $blocks */
        foreach ($blocks->getBlocks() as $block) {
          $this->data['blocks']['rendered'][] = array(
            'id' => $block->id,
            'region' => $block->get('region'),
            'status' => $block->get('status'),
            'theme' => $block->get('theme'),
            'plugin' => $block->get('plugin'),
            'settings' => $block->get('settings'),
          );
        }
      }
    }
  }

  /**
   * @return array
   */
  public function getRenderedBlocks() {
    return $this->data['blocks']['rendered'];
  }

  /**
   * @return int
   */
  public function getRenderedBlocksCount() {
    return count($this->getRenderedBlocks());
  }

  /**
   * @return array
   */
  public function getLoadedBlocks() {
    return $this->data['blocks']['loaded'];
  }

  /**
   * @return int
   */
  public function getLoadedBlocksCount() {
    return count($this->getLoadedBlocks());
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'block';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Block');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total loaded blocks: @loaded, total rendered blocks: @rendered', array(
      '@loaded' => $this->getLoadedBlocksCount(),
      '@rendered' => $this->getRenderedBlocksCount()
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    /** @var EntityManager $entity_manager */
    $entity_manager = \Drupal::service('entity.manager');
    $storage = $entity_manager->getStorage('block');

    if ($this->getLoadedBlocks()) {
      $build = array_merge($build, $this->getTable($this->getLoadedBlocks(), $storage, $this->t('Loaded blocks')));
    }

    if ($this->getRenderedBlocks()) {
      $build = array_merge($build, $this->getTable($this->getRenderedBlocks(), $storage, $this->t('Rendered blocks')));
    }

    return $build;
  }

  /**
   * @param $blocks
   * @param EntityStorageInterface $storage
   *
   * @return mixed
   */
  private function getTable($blocks, $storage, $title) {
    $rows = array();
    foreach ($blocks as $block) {
      $row = array();

      /** @var Block $entity */
      $entity = $storage->load($block['id']);

      $operations = array();
      if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
        $operations['edit'] = array(
            'title' => $this->t('Edit'),
            'weight' => 10,
          ) + $entity->urlInfo('edit-form')->toArray();
      }

      $row[] = $entity->id();
      $row[] = $block['settings']['label'];
      $row[] = $block['settings']['provider'];
      $row[] = ($block['region'] == -1) ? $this->t('No region') : $block['region'];
      $row[] = $block['theme'];
      $row[] = ($block['status']) ? $this->t('Enabled') : $this->t('Disabled');
      $row[] = $block['plugin'];
      $row[] = array(
        'data' => array(
          '#type' => 'operations',
          '#links' => $operations,
        ),
      );

      $rows[] = $row;
    }

    $header = array(
      $this->t('Id'),
      $this->t('Label'),
      $this->t('Provider'),
      $this->t('Region'),
      array(
        'data' => $this->t('Theme'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      array(
        'data' => $this->t('Status'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      array(
        'data' => $this->t('Plugin'),
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      $this->t('Operations'),
    );

    $build[] = array(
      array(
        '#markup' => '<h3>' . $title . '</h3>',
      ),
    );

    $build[] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#sticky' => TRUE,
    );

    return $build;
  }

}
