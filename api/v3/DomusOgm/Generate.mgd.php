<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:DomusOgm.Generate',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Domus Medica OGM genereren',
      'description' => 'Genereer OGM voor bijdragen met invoice',
      'run_frequency' => 'Daily',
      'api_entity' => 'DomusOgm',
      'api_action' => 'Generate',
      'parameters' => '',
      'is_active' => 0
    ),
  ),
);