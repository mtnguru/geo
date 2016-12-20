<?php

namespace Drupal\Tests\workspace\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the workspace entity.
 *
 * @group workspace
 */
class WorkspaceTest extends BrowserTestBase {
  use WorkspaceTestUtilities;

  public static $modules = ['workspace'];

  public function testSpecialCharacters() {
    $permissions = [
      'access administration pages',
      'administer site configuration',
      'create_workspace',
      'edit_own_workspace',
    ];

    $editor1 = $this->drupalCreateUser($permissions);
    $this->drupalLogin($editor1);

    // Test a valid workspace name
    $this->createWorkspaceThroughUI('Workspace 1', 'a0_$()+-/');

    // Test and invaid workspace name
    $this->drupalGet('/admin/structure/workspace/add');
    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());
    $page = $session->getPage();
    $page->fillField('label', 'workspace2');
    $page->fillField('machine_name', 'A!"£%^&*{}#~@?');
    $page->findButton(t('Save'))->click();
    $session->getPage()->hasContent("This value is not valid");
  }
  
}
