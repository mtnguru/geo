<?php

namespace Drupal\workspace\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;
use Drupal\workspace\WorkspacePointerInterface;

/**
 * Defines the Replication entity.
 *
 * @ingroup workspace
 *
 * @ContentEntityType(
 *   id = "replication",
 *   label = @Translation("Deployment"),
 *   base_table = "replication",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   local = TRUE,
 *   multiversion = FALSE
 * )
 */
class Replication extends ContentEntityBase implements ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Replication entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Replication entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Created by'))
      ->setDescription(t('The user ID of person who started the deployment.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\workspace\Entity\Replication::getCurrentUserId');

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'weight' => -3,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Source'))
      ->setDescription(t('The source endpoint.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'workspace_pointer')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'weight' => -2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['target'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Target'))
      ->setDescription(t('The target endpoint.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'workspace_pointer')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'weight' => -1,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['replicated'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Deployed'))
      ->setDescription(t('The time that the entity was deployed.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return array(\Drupal::currentUser()->id());
  }

  public static function generateReplicationId(WorkspacePointerInterface $source, WorkspacePointerInterface $target) {
    return \md5(
      $source->getWorkspace()->getMachineName() .
      $target->getWorkspace()->getMachineName()
    );
  }
}
