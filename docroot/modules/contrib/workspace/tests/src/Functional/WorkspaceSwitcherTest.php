<?php

namespace Drupal\Tests\workspace\Functional;

use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests workspace switching functionality.
 *
 * @group workspace
 */
class WorkspaceSwitcherTest extends BrowserTestBase {
  use WorkspaceTestUtilities;
  use BlockCreationTrait {
    placeBlock as drupalPlaceBlock;
  }

  public static $modules = ['block', 'workspace'];

  /**
   * Test that the block displays and switches workspaces.
   * Then test the admin page displays workspaces and allows switching.
   */
  public function testSwitchingWorkspaces() {
    $permissions = [
      'create_workspace',
      'edit_own_workspace',
      'view_own_workspace',
      'bypass_entity_access_own_workspace',
    ];

    $this->setupWorkspaceSwitcherBlock();

    $mayer = $this->drupalCreateUser($permissions);
    $this->drupalLogin($mayer);

    $vultures = $this->createWorkspaceThroughUI('Vultures', 'vultures');
    $this->switchToWorkspace($vultures);

    $gravity = $this->createWorkspaceThroughUI('Gravity', 'gravity');

    $this->drupalGet('/admin/structure/workspace/' . $gravity->id() . '/activate');

    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->findButton(t('Activate'))->click();

    $session->getPage()->findLink($gravity->label());
  }
  
}
