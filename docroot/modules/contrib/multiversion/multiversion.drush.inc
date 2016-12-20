<?php

use Psr\Log\LogLevel;

/**
 * Implements of hook_drush_command().
 */
function multiversion_drush_command() {
  $items = array();

  $items['multiversion-uninstall'] = array(
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    'description' => 'Uninstall Multiversion.',
    'aliases' => array('mun'),
  );

  return $items;
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_multiversion_uninstall() {
  $extension = 'multiversion';
  $uninstall = TRUE;
  $extension_info = drush_get_extensions();
  $required = drush_drupal_required_modules($extension_info);
  if (in_array($extension, $required)) {
    $info = $extension_info[$extension]->info;
    $explanation = !empty($info['explanation']) ? ' ' . dt('Reason: !explanation.', array('!explanation' => strip_tags($info['explanation'])))  : '';
    drush_log(dt('!extension is a required extension and can\'t be uninstalled.', array('!extension' => $extension)) . $explanation, LogLevel::INFO);
    $uninstall = FALSE;
  }
  elseif (!$extension_info[$extension]->status) {
    drush_log(dt('!extension is already uninstalled.', array('!extension' => $extension)), LogLevel::INFO);
    $uninstall = FALSE;
  }
  elseif (drush_extension_get_type($extension_info[$extension]) == 'module') {
    $dependents = array();
    foreach (drush_module_dependents(array($extension), $extension_info) as $dependent) {
      if (!in_array($dependent, $required) && ($extension_info[$dependent]->status)) {
        $dependents[] = $dependent;
      }
    }
    if (count($dependents)) {
      drush_log(dt('To uninstall !extension, the following extensions must be uninstalled first: !required', array('!extension' => $extension, '!required' => implode(', ', $dependents))), LogLevel::ERROR);
      $uninstall = FALSE;
    }
  }

  if ($uninstall) {
    drush_print(dt('Multiversion will be uninstalled.'));
    if(!drush_confirm(dt('Do you really want to continue?'))) {
      return drush_user_abort();
    }

    drush_log('*** ' . dt('The uninstall process can take a few minutes, it depends by the number of entities on the site. Please be patient.'), LogLevel::WARNING);

    \Drupal::service('multiversion.manager')->disableEntityTypes();
    drush_module_uninstall(['multiversion']);

    // Inform the user of final status.
    drush_log(dt('!extension was successfully uninstalled.', array('!extension' => $extension)), LogLevel::INFO);
  }

}
