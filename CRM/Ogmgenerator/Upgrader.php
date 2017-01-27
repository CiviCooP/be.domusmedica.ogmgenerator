<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Ogmgenerator_Upgrader extends CRM_Ogmgenerator_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Create table domus_inv_fin_type on install.
   */
  public function install() {
    $this->executeSqlFile('sql/createDomusInvFinType.sql');
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    $domusInvFinType->updateAll();
  }

  /**
   * Drop table domus_inv_fin_type on uninstall
   */
  public function uninstall() {
   if (CRM_Core_DAO::checkTableExists('domus_inv_fin_type')) {
     CRM_Core_DAO::executeQuery('DROP TABLE domus_inv_fin_type');
   }
  }
}
