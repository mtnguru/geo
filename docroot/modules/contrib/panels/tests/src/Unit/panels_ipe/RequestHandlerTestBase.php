<?php

namespace Drupal\Tests\panels\Unit\panels_ipe;


use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels\Storage\PanelsStorageManagerInterface;
use Drupal\Tests\RandomGeneratorTrait;
use Drupal\user\SharedTempStore;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base tests for IPE request handler classes.
 */
abstract class RequestHandlerTestBase extends \PHPUnit_Framework_TestCase {

  use RandomGeneratorTrait;

  /** @var  \Drupal\panels_ipe\Helpers\RequestHandlerInterface */
  protected $sut;

  /** @var PHPUnit_Framework_MockObject_MockObject */
  protected $moduleHandler;

  /** @var PHPUnit_Framework_MockObject_MockObject */
  protected $panelsStore;

  /** @var PHPUnit_Framework_MockObject_MockObject */
  protected $tempStore;

  /** @var PHPUnit_Framework_MockObject_MockObject */
  protected $panelsDisplay;

  public function setUp() {
    parent::setUp();
    $this->moduleHandler = $this->getMockForAbstractClass(ModuleHandlerInterface::class);
    $this->panelsStore = $this->getMockForAbstractClass(PanelsStorageManagerInterface::class);
    $this->tempStore = $this->getMockBuilder(SharedTempStore::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->panelsDisplay = $this->getMockBuilder(PanelsDisplayVariant::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->panelsDisplay->method('id')->willReturn(
      $this->randomMachineName(16)
    );
  }

  protected function createRequest($content = NULL) {
    return new Request([], [], [], [], [], [], $content);
  }

  /**
   * @test
   */
  public function emptyRequestResultsInFailedResponse() {
    $this->sut->handleRequest($this->panelsDisplay, $this->createRequest());

    $expected = new JsonResponse(['success' => FALSE], 400);
    $this->assertEquals($expected, $this->sut->getJsonResponse());
  }
}
