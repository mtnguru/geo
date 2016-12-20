<?php

namespace Drupal\multiversion\Tests;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Test the UninstallTest class.
 *
 * @group multiversion
 */
class UninstallTest extends WebTestBase {

  protected $strictConfigSchema = FALSE;

  /**
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  /**
   * @var array
   */
  protected $entityTypes = [
    'node' => ['type' => 'article', 'title' => 'foo'],
//    'taxonomy_term' => ['name' => 'A term', 'vid' => 123],
//    'comment' => [
//      'entity_type' => 'node',
//      'field_name' => 'comment',
//      'subject' => 'How much wood would a woodchuck chuck',
//      'comment_type' => 'comment',
//    ],
//    'block_content' => [
//      'info' => 'New block',
//      'type' => 'basic',
//    ],
    'menu_link_content' => [
      'menu_name' => 'menu_test',
      'bundle' => 'menu_link_content',
      'link' => [['uri' => 'user-path:/']],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'comment',
    'menu_link_content',
    'block_content',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->moduleInstaller = \Drupal::service('module_installer');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    $this->drupalLogin($this->rootUser);
  }

  public function testDisableWithExistingContent() {
    // Install Multiversion.
    $this->moduleInstaller->install(['multiversion']);

    // Check if all updates have been applied.
    $this->assertFalse(\Drupal::service('entity.definition_update_manager')->needsUpdates(), 'All compatible entity types have been updated.');

    foreach ($this->entityTypes as $entity_type_id => $values) {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
      $count = 2;
      for ($i = 0; $i < $count; $i++) {
        $storage->create($values)->save();
      }
      $count_before[$entity_type_id] = $count;
    }

    // Disable entity types.
    /** @var \Drupal\multiversion\MultiversionManagerInterface $manager */
    $manager = \Drupal::getContainer()->get('multiversion.manager');
    $manager->disableEntityTypes();
    // Uninstall Multiversion.
    $this->moduleInstaller->uninstall(['multiversion']);

    /** @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $update_manager */
    $update_manager = \Drupal::service('entity.definition_update_manager');
    // The field class for the UUID field that Multiversion provides will now
    // be gone. So we need to apply updates.
    $update_manager->applyUpdates();
    // Check that applying updates worked.
    $this->assertFalse($update_manager->needsUpdates(), 'There are not new updates to apply.');

    $ids_after = [];
    $manager = \Drupal::entityTypeManager();
    // Now check that the previously created entities still exist, have the
    // right IDs and are multiversion enabled.
    foreach ($this->entityTypes as $entity_type_id => $values) {
      $storage = $manager->getStorage($entity_type_id);
      $storage_class = $storage->getEntityType($entity_type_id)->getStorageClass();
      $this->assertFalse(strpos($storage_class, 'Drupal\multiversion\Entity\Storage'), "$entity_type_id got the correct storage handler assigned.");
      $this->assertTrue($storage->getQuery() instanceof QueryInterface, "$entity_type_id got the correct query handler assigned.");
      $ids_after[$entity_type_id] = $storage->getQuery()->execute();
      $this->assertEqual($count_before[$entity_type_id], count($ids_after[$entity_type_id]), "All ${entity_type_id}s were migrated.");
    }
  }

}
