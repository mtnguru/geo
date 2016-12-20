<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface WorkspaceInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the last sequence ID in the workspace's sequence index.
   *
   * @return float
   */
  public function getUpdateSeq();

  /**
   * Sets the workspace creation timestamp.
   *
   * @param int $timestamp
   *   The workspace creation timestamp.
   *
   * @return \Drupal\multiversion\Entity\WorkspaceInterface
   *   The called workspace entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the workspace creation timestamp.
   *
   * @return int
   *   Creation timestamp of the workspace.
   */
  public function getStartTime();

  /**
   * Returns the workspace machine name.
   *
   * @return string
   *   Machine name of the workspace.
   */
  public function getMachineName();

}
