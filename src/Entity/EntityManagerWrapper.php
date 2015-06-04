<?php

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\webprofiler\Entity\Block\BlockStorageDecorator;
use Drupal\webprofiler\Entity\Block\BlockViewBuilderDecorator;

class EntityManagerWrapper extends EntityManager {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface $entityManager
   */
  private $entityManager;

  /**
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
  }

  /**
   * @var array[EntityStorageInterface]
   */
  private $loaded;

  /**
   * @var array[EntityViewBuilderInterface]
   */
  private $rendered;

  public function clearCachedDefinitions() {
    $this->entityManager->clearCachedDefinitions();
  }

  protected function findDefinitions() {
    return $this->entityManager->clearCachedDefinitions();
  }

  public function getDefinition($entity_type_id, $exception_on_invalid = TRUE) {
    return $this->entityManager->getDefinition($entity_type_id, $exception_on_invalid);
  }

  public function hasHandler($entity_type, $handler_type) {
    $this->entityManager->hasHandler($entity_type, $handler_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage($entity_type) {
    $controller = $this->getHandler($entity_type, 'storage');

    if ('block' == $entity_type) {
      $decorator = new BlockStorageDecorator($controller);
      $this->loaded[] = $decorator;

      return $decorator;
    }

    return $controller;
  }

  public function getListBuilder($entity_type) {
    return $this->entityManager->getListBuilder($entity_type);
  }

  public function getFormObject($entity_type, $operation) {
    return $this->entityManager->getFormObject($entity_type, $operation);
  }

  public function getRouteProviders($entity_type) {
    return $this->entityManager->getRouteProviders($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBuilder($entity_type) {
    $controller = $this->getHandler($entity_type, 'view_builder');

    if ('block' == $entity_type) {
      $decorator = new BlockViewBuilderDecorator($controller);
      $this->rendered[] = $decorator;

      return $decorator;
    }

    return $controller;
  }

  public function getAccessControlHandler($entity_type) {
    return $this->entityManager->getAccessControlHandler($entity_type);
  }

  public function getHandler($entity_type, $handler_type) {
    return $this->entityManager->getHandler($entity_type, $handler_type);
  }

  public function createHandlerInstance($class, EntityTypeInterface $definition = null) {
    $this->entityManager->createHandlerInstance($class, $definition);
  }

  public function getBaseFieldDefinitions($entity_type_id) {
    return $this->entityManager->getBaseFieldDefinitions($entity_type_id);
  }

  protected function buildBaseFieldDefinitions($entity_type_id) {
    return $this->entityManager->buildBaseFieldDefinitions($entity_type_id);
  }

  public function getFieldDefinitions($entity_type_id, $bundle) {
    return $this->entityManager->getFieldDefinitions($entity_type_id, $bundle);
  }

  protected function buildBundleFieldDefinitions($entity_type_id, $bundle, array $base_field_definitions) {
    return $this->entityManager->buildBundleFieldDefinitions($entity_type_id, $bundle, $base_field_definitions);
  }

    public function getFieldStorageDefinitions($entity_type_id) {
    return $this->entityManager->getFieldStorageDefinitions($entity_type_id);
  }

  public function getFieldMap() {
    return $this->entityManager->getFieldMap();
  }

  public function getFieldMapByFieldType($field_type) {
    return $this->entityManager->getFieldMapByFieldType($field_type);
  }

  public function onFieldDefinitionCreate(FieldDefinitionInterface $field_definition) {
    $this->entityManager->onFieldDefinitionCreate($field_definition);
  }

  public function onFieldDefinitionUpdate(FieldDefinitionInterface $field_definition, FieldDefinitionInterface $original) {
    $this->entityManager->onFieldDefinitionUpdate($field_definition, $original);
  }

  public function onFieldDefinitionDelete(FieldDefinitionInterface $field_definition) {
    $this->entityManager->onFieldDefinitionDelete($field_definition);
  }

  protected function buildFieldStorageDefinitions($entity_type_id) {
    return $this->entityManager->buildFieldStorageDefinitions($entity_type_id);
  }

  public function clearCachedFieldDefinitions() {
    $this->entityManager->clearCachedFieldDefinitions();
  }

  public function clearCachedBundles() {
    $this->entityManager->clearCachedBundles();
  }

  public function getBundleInfo($entity_type) {
    return $this->entityManager->getBundleInfo($entity_type);
  }

  public function getAllBundleInfo() {
    return $this->entityManager->getAllBundleInfo();
  }

  public function getExtraFields($entity_type_id, $bundle) {
    return $this->entityManager->getExtraFields($entity_type_id, $bundle);
  }

  public function getEntityTypeLabels($group = FALSE) {
    return $this->entityManager->getEntityTypeLabels($group);
  }

  public function getTranslationFromContext(EntityInterface $entity, $langcode = NULL, $context = array()) {
    return $this->entityManager->getTranslationFromContext($entity, $langcode, $context);
  }

  public function getAllViewModes() {
    return $this->entityManager->getAllViewModes();
  }

  public function getViewModes($entity_type_id) {
    return $this->entityManager->getViewModes($entity_type_id);
  }

  public function getAllFormModes() {
    return $this->entityManager->getAllFormModes();
  }

  public function getFormModes($entity_type_id) {
    return $this->entityManager->getFormModes($entity_type_id);
  }

  protected function getAllDisplayModesByEntityType($display_type) {
    return $this->entityManager->getAllDisplayModesByEntityType($display_type);
  }

  protected function getDisplayModesByEntityType($display_type, $entity_type_id) {
    return $this->entityManager->getDisplayModesByEntityType($display_type, $entity_type_id);
  }

  public function getViewModeOptions($entity_type, $include_disabled = FALSE) {
    return $this->entityManager->getViewModeOptions($entity_type, $include_disabled);
  }

  protected function getDisplayModeOptions($display_type, $entity_type_id, $include_disabled = FALSE) {
    return $this->entityManager->getDisplayModeOptions($display_type, $entity_type_id, $include_disabled);
  }

  public function getFormModeOptions($entity_type, $include_disabled = FALSE) {
    return $this->entityManager->getFormModeOptions($entity_type, $include_disabled);
  }

  public function loadEntityByUuid($entity_type_id, $uuid) {
    return $this->entityManager->loadEntityByUuid($entity_type_id, $uuid);
  }

  public function loadEntityByConfigTarget($entity_type_id, $target) {
    return $this->entityManager->loadEntityByConfigTarget($entity_type_id, $target);
  }

  public function getEntityTypeFromClass($class_name) {
    return $this->entityManager->getEntityTypeFromClass($class_name);
  }

  public function onEntityTypeCreate(EntityTypeInterface $entity_type) {
    $this->entityManager->onEntityTypeCreate($entity_type);
  }

  public function onEntityTypeUpdate(EntityTypeInterface $entity_type, EntityTypeInterface $original) {
    $this->entityManager->onEntityTypeUpdate($entity_type, $original);
  }

  public function onEntityTypeDelete(EntityTypeInterface $entity_type) {
    $this->entityManager->onEntityTypeDelete($entity_type);
  }

  public function onFieldStorageDefinitionCreate(FieldStorageDefinitionInterface $storage_definition) {
    $this->entityManager->onFieldStorageDefinitionCreate($storage_definition);
  }

  public function onFieldStorageDefinitionUpdate(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original) {
    $this->entityManager->onFieldStorageDefinitionUpdate($storage_definition, $original);
  }

  public function onFieldStorageDefinitionDelete(FieldStorageDefinitionInterface $storage_definition) {
    $this->entityManager->onFieldStorageDefinitionDelete($storage_definition);
  }

  public function onBundleCreate($bundle, $entity_type_id) {
    $this->entityManager->onBundleCreate($bundle, $entity_type_id);
  }

  public function onBundleRename($bundle_old, $bundle_new, $entity_type_id) {
    $this->entityManager->onBundleRename($bundle_old, $bundle_new, $entity_type_id);
  }

  public function onBundleDelete($bundle, $entity_type_id) {
    $this->entityManager->onBundleDelete($bundle, $entity_type_id);
  }

  public function getLastInstalledDefinition($entity_type_id) {
    return $this->entityManager->getLastInstalledDefinition($entity_type_id);
  }

  public function useCaches($use_caches = FALSE) {
    $this->entityManager->useCaches($use_caches);
  }

  protected function setLastInstalledDefinition(EntityTypeInterface $entity_type) {
    $this->entityManager->setLastInstalledDefinition($entity_type);
  }

  protected function deleteLastInstalledDefinition($entity_type_id) {
    $this->entityManager->deleteLastInstalledDefinition($entity_type_id);
  }

  public function getLastInstalledFieldStorageDefinitions($entity_type_id) {
    return $this->entityManager->getLastInstalledFieldStorageDefinitions($entity_type_id);
  }

  protected function setLastInstalledFieldStorageDefinitions($entity_type_id, array $storage_definitions) {
    $this->entityManager->setLastInstalledFieldStorageDefinitions($entity_type_id, $storage_definitions);
  }

  protected function setLastInstalledFieldStorageDefinition(FieldStorageDefinitionInterface $storage_definition) {
    $this->entityManager->setLastInstalledFieldStorageDefinition($storage_definition);
  }

  protected function deleteLastInstalledFieldStorageDefinition(FieldStorageDefinitionInterface $storage_definition) {
    $this->entityManager->deleteLastInstalledFieldStorageDefinition($storage_definition);
  }

  /**
   * @return array[EntityStorageInterface]
   */
  public function getLoaded() {
    return $this->loaded;
  }

  /**
   * @return array[EntityViewBuilderInterface]
   */
  public function getRendered() {
    return $this->rendered;
  }

}
