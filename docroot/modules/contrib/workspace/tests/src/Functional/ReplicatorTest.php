<?php

namespace Drupal\Tests\workspace\Functional;

use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\workspace\ReplicatorManager;

/**
 * Test the workspace entity.
 *
 * @group workspace
 */
class ReplicatorTest extends BrowserTestBase {

  use WorkspaceTestUtilities;

  use BlockCreationTrait {
    placeBlock as drupalPlaceBlock;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'node',
    'user',
    'block',
    'workspace',
    'multiversion',
    'taxonomy',
    'entity_reference',
    'field',
    'field_ui',
    'field_test',
    'menu_link_content',
    'menu_ui'
  ];

  public function setUp() {
    parent::setUp();
    $permissions = [
      'create_workspace',
      'edit_own_workspace',
      'view_own_workspace',
      'create test content',
      'access administration pages',
      'administer taxonomy',
      'administer menu',
      'access content overview',
      'administer content types',
      'administer node display',
      'administer node fields',
      'administer node form display',
    ];

    $this->createNodeType('Test', 'test');
    $vocabulary = Vocabulary::create(['name' => 'Tags', 'vid' => 'tags', 'hierarchy' => 0]);
    $vocabulary->save();

    $test_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($test_user);
    $this->setupWorkspaceSwitcherBlock();
  }

  /**
   * Verifies that a user can edit anything in a workspace with a specific perm.
   */
  public function testReplication() {
    $live = $this->getOneEntityByLabel('workspace', 'Live');
    $this->drupalGet('/node/add/test');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->fillField('Title', 'Test node');
    $page->fillField('Provide a menu link', 1);
    $page->fillField('Menu link title', 'Test node link');
    $page->findButton(t('Save'))->click();
    $page = $session->getPage();
    $page->hasContent("Test node has been created");

    $test_node_live = $this->getOneEntityByLabel('node', 'Test node');
    $this->assertEquals($live->id(), $test_node_live->get('workspace')->entity->id());
    $this->drupalGet('/admin/content');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->hasContent($test_node_live->label());

    $menu_link_live = $this->getOneEntityByLabel('menu_link_content', 'Test node link');
    $this->assertEquals($live->id(), $menu_link_live->get('workspace')->entity->id());
    $this->drupalGet('/admin/structure/menu/manage/main');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->hasContent('Test node link');

    $target = $this->createWorkspaceThroughUI('Target', 'target');
    /** @var ReplicatorManager $rm */
    $rm = \Drupal::service('workspace.replicator_manager');
    $rm->replicate($this->getPointerToWorkspace($live), $this->getPointerToWorkspace($target));

    $this->switchToWorkspace($target);

    $test_node_target = $this->getOneEntityByLabel('node', 'Test node');
    $this->assertEquals($target->id(), $test_node_target->get('workspace')->entity->id());
    $this->drupalGet('/admin/content');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->hasContent($test_node_target->label());

    $menu_link_target = $this->getOneEntityByLabel('menu_link_content', 'Test node link');
    $this->assertEquals($target->id(), $menu_link_target->get('workspace')->entity->id());
    $this->drupalGet('/admin/structure/menu/manage/main');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->hasContent('Test node link');
  }

}
