<?php

namespace Drupal\workspace\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\workspace\WorkspacePointerInterface;

/**
 * Defines the Workspace pointer entity.
 *
 * @ingroup workspace
 *
 * @ContentEntityType(
 *   id = "workspace_pointer",
 *   label = @Translation("Workspace pointer"),
 *   base_table = "workspace_pointer",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   local = TRUE,
 *   multiversion = FALSE
 * )
 */
class WorkspacePointer extends ContentEntityBase implements WorkspacePointerInterface {
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  /**
   * @inheritDoc
   */
  public function label() {
    if (!empty($this->getWorkspace())) {
      return $this->getWorkspace()->label();
    }

    return parent::label();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkspace(WorkspaceInterface $workspace) {
    $this->set('workspace_pointer', $workspace->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkspaceId($workspace_id) {
    $this->set('workspace_pointer', $workspace_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspace() {
    $ws = $this->get('workspace_pointer')->entity;
    return $ws;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId() {
    return $this->get('workspace_pointer')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function generateReplicationId(WorkspacePointerInterface $target) {
    $target_name = $target->label();
    if ($target->getWorkspace() instanceof WorkspaceInterface) {
      $target_name = $target->getWorkspace()->getMachineName();
    }
    return \md5(
      $this->getWorkspace()->getMachineName() .
      $target_name
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Workspace pointer entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Workspace pointer entity.'))
      ->setReadOnly(TRUE);
    $fields['revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The revision ID of the workspace pointer entity.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Workspace pointer entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setRevisionable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setRevisionable(TRUE);

    $fields['workspace_pointer'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Workspace'))
      ->setDescription(t('A reference to the workspace'))
      ->setSetting('target_type', 'workspace')
      ->setRevisionable(TRUE);

    return $fields;
  }

}
