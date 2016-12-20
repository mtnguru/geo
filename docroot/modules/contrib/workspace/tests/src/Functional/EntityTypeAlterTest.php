<?php

namespace Drupal\Tests\workspace\Functional;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the workspace entity.
 *
 * @group workspace
 */
class EntityTypeAlterTest extends BrowserTestBase {
  use WorkspaceTestUtilities;

  public static $modules = ['node', 'user', 'workspace', 'multiversion'];

  public function testEntityTypeAlter() {
    $entity_types = \Drupal::service('entity_type.manager')->getDefinitions();

    /** @var EntityTypeInterface $workspace_type */
    $workspace_type = $entity_types['workspace_type'];
    $this->assertTrue($workspace_type->getHandlerClass('list_builder') !== null);
    $this->assertTrue($workspace_type->getHandlerClass('route_provider', 'html') !== null);
    $this->assertTrue($workspace_type->getHandlerClass('form', 'default') !== null);
    $this->assertTrue($workspace_type->getHandlerClass('form', 'add') !== null);
    $this->assertTrue($workspace_type->getHandlerClass('form', 'edit') !== null);
    $this->assertTrue($workspace_type->getHandlerClass('form', 'delete') !== null);
    $this->assertTrue($workspace_type->getLinkTemplate('collection') !== null);
    $this->assertTrue($workspace_type->getLinkTemplate('edit-form') !== null);
    $this->assertTrue($workspace_type->getLinkTemplate('delete-form') !== null);

    /** @var EntityTypeInterface $workspace */
    $workspace = $entity_types['workspace'];
    $this->assertTrue($workspace->getHandlerClass('list_builder') !== null);
    $this->assertTrue($workspace->getHandlerClass('route_provider', 'html') !== null);
    $this->assertTrue($workspace->getHandlerClass('form', 'default') !== null);
    $this->assertTrue($workspace->getHandlerClass('form', 'add') !== null);
    $this->assertTrue($workspace->getHandlerClass('form', 'edit') !== null);
    $this->assertTrue($workspace->getLinkTemplate('collection') !== null);
    $this->assertTrue($workspace->getLinkTemplate('edit-form') !== null);
    $this->assertTrue($workspace->getLinkTemplate('activate-form') !== null);
    $this->assertTrue($workspace->get('field_ui_base_route') !== null);

    foreach ($entity_types as $entity_type) {
      if (\Drupal::service('multiversion.manager')->isSupportedEntityType($entity_type)) {
        if ($entity_type->hasViewBuilderClass() && $entity_type->hasLinkTemplate('canonical')) {
          $this->assertTrue($entity_type->getLinkTemplate('version-tree') !== null);
          $this->assertTrue($entity_type->getLinkTemplate('revision') !== null);
        }
      }
    }
  }

  public function testTree() {
    $permissions = [
      'create_workspace',
      'edit_own_workspace',
      'view_own_workspace',
      'create test content',
      'view_revision_trees'
    ];
    $this->createNodeType('Test', 'test');
    $clapton = $this->drupalCreateUser($permissions);
    $this->drupalLogin($clapton);

    $layla_node = $this->createNodeThroughUI('Layla', 'test');
    $this->drupalGet('/node/' . $layla_node->id() . '/tree');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->findLink($this->truncateRev($layla_node->_rev->value));
  }

  protected function truncateRev($rev) {
    list($i) = explode('-', $rev);
    $length = strlen($i) + 9;
    return Unicode::truncate($rev, $length, FALSE, TRUE);
  }

}
