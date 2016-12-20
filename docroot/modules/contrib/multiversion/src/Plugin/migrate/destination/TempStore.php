<?php

namespace Drupal\multiversion\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "tempstore"
 * )
 */
class TempStore extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * Time to live in seconds until the storage expire.
   *
   * @var int
   */
  protected $expire = 604800;

  /**
   * @var KeyValueStoreExpirableInterface
   */
  protected $tempStore;

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
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    list($entity_type_id) = explode('__', $migration->id());
    $entity_type = $entity_manager->getDefinition($entity_type_id);

    $this->entityTypeId = $entity_type_id;
    $this->entityIdKey = $entity_type->getKey('id');
    $this->tempStore = $temp_store_factory->get('multiversion_migration_' . $this->entityTypeId);
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    $source = $row->getSource();
    $this->tempStore->setWithExpire($source['uuid'], $source, $this->expire);
    return array($this->entityIdKey => $source[$this->entityIdKey]);
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
  public function fields(MigrationInterface $migration = NULL) {
    return array();
  }

}
