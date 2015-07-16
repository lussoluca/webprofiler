<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\BlocksDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class BlocksDataCollector
 */
class BlocksDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var
   */
  private $entityManager;

  /**
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $loaded = $this->entityManager->getLoaded('block');
    $rendered = $this->entityManager->getRendered('block');

    $this->data['blocks'] = [];

    if ($loaded) {
      /** @var \Drupal\webprofiler\Entity\EntityStorageDecorator $loaded */
      /** @var \Drupal\block\BlockInterface $block */
      foreach ($loaded->getEntities() as $block) {
        $this->data['blocks']['loaded'][] = [
          'id' => $block->get('id'),
          'region' => $block->getRegion(),
          'status' => $block->get('status'),
          'theme' => $block->getTheme(),
          'plugin' => $block->get('plugin'),
          'settings' => $block->get('settings'),
        ];
      }
    }

    if ($rendered) {
      /** @var \Drupal\webprofiler\Entity\EntityStorageDecorator $rendered */
      /** @var \Drupal\block\BlockInterface $block */
      foreach ($rendered->getEntities() as $block) {
        $this->data['blocks']['rendered'][] = [
          'id' => $block->get('id'),
          'region' => $block->getRegion(),
          'status' => $block->get('status'),
          'theme' => $block->getTheme(),
          'plugin' => $block->get('plugin'),
          'settings' => $block->get('settings'),
        ];
      }
    }
  }

  /**
   * @return array
   */
  public function getRenderedBlocks() {
    return (array_key_exists('rendered', $this->data['blocks'])) ? $this->data['blocks']['rendered'] : [];
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
    return (array_key_exists('loaded', $this->data['blocks'])) ? $this->data['blocks']['loaded'] : [];
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
    return 'blocks';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Blocks');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total loaded blocks: @loaded, total rendered blocks: @rendered', [
      '@loaded' => $this->getLoadedBlocksCount(),
      '@rendered' => $this->getRenderedBlocksCount()
    ]);
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    $build = array();
//
//    /** @var EntityManager $entity_manager */
//    $entity_manager = \Drupal::service('entity.manager');
//    $storage = $entity_manager->getStorage('block');
//
//    if ($this->getLoadedBlocks()) {
//      $build['loaded'] = $this->getTable($this->getLoadedBlocks(), $storage, $this->t('Loaded blocks'));
//    }
//
//    if ($this->getRenderedBlocks()) {
//      $build['rendered'] = $this->getTable($this->getRenderedBlocks(), $storage, $this->t('Rendered blocks'));
//    }
//
//    return $build;
//  }

//  /**
//   * @param $blocks
//   * @param EntityStorageInterface $storage
//   *
//   * @return mixed
//   */
//  private function getTable($blocks, $storage, $title) {
//    $rows = array();
//    foreach ($blocks as $block) {
//      $row = array();
//
//      /** @var Block $entity */
//      $entity = $storage->load($block['id']);
//
//      $operations = array();
//      if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
//        $operations['edit'] = array(
//          'title'  => $this->t('Edit'),
//          'weight' => 10,
//          'url' => $entity->urlInfo('edit-form'),
//        );
//      }
//
//      $row[] = $entity->id();
//      $row[] = $block['settings']['label'];
//      $row[] = $block['settings']['provider'];
//      $row[] = ($block['region'] == -1) ? $this->t('No region') : $block['region'];
//      $row[] = $block['theme'];
//      $row[] = ($block['status']) ? $this->t('Enabled') : $this->t('Disabled');
//      $row[] = $block['plugin'];
//      $row[] = array(
//        'data' => array(
//          '#type' => 'operations',
//          '#links' => $operations,
//        ),
//      );
//
//      $rows[] = $row;
//    }
//
//    $header = array(
//      $this->t('Id'),
//      $this->t('Label'),
//      $this->t('Provider'),
//      $this->t('Region'),
//      array(
//        'data' => $this->t('Theme'),
//        'class' => array(RESPONSIVE_PRIORITY_LOW),
//      ),
//      array(
//        'data' => $this->t('Status'),
//        'class' => array(RESPONSIVE_PRIORITY_LOW),
//      ),
//      array(
//        'data' => $this->t('Plugin'),
//        'class' => array(RESPONSIVE_PRIORITY_LOW),
//      ),
//      $this->t('Operations'),
//    );
//
//    $build['title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>{{ title }}</h3>',
//      '#context' => array(
//        'title' => $title,
//      ),
//    );
//
//    $build['table'] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => $header,
//      '#sticky' => TRUE,
//    );
//
//    return $build;
//  }

}
