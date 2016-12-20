<?php

namespace Drupal\multiversion\Plugin\migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase as CoreSourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SourcePluginBase extends CoreSourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var string
   */
  protected $entityTypeId;

  /**
   * @var string
   */
  protected $entityIdKey;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')
    );
  }

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param MigrationInterface $migration
   *   The migration.
   * @param EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->entityManager = $entity_manager;

    list($entity_type_id) = explode('__', $migration->id());
    $entity_type = $entity_manager->getDefinition($entity_type_id);

    $this->entityTypeId = $entity_type_id;
    $this->entityIdKey = $entity_type->getKey('id');
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      $this->entityIdKey => array(
        'type' => 'integer',
        'alias' => 'base',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return '';
  }

}
