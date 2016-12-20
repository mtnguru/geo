<?php

namespace Drupal\multiversion\Plugin\migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User source from json file.
 *
 * @MigrateSource(
 *   id = "tempstore"
 * )
 */
class TempStore extends SourcePluginBase {

  /**
   * @var KeyValueStoreExpirableInterface
   */
  protected $tempStore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager'),
      $container->get('keyvalue.expirable')
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
   * @param KeyValueExpirableFactoryInterface $temp_store_factory
   *   The temp store factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityManagerInterface $entity_manager, KeyValueExpirableFactoryInterface $temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $entity_manager);
    $this->tempStore = $temp_store_factory->get('multiversion_migration_' . $this->entityTypeId);
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $values = $this->tempStore->getAll();
    return new \ArrayIterator($values);
  }

}
