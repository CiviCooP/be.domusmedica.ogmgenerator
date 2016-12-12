<?php

/**
 * DomusOgm.Generate API (genereer OGM op basis van invoice nummer)
 *
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date Nov 2016
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_domus_ogm_Generate($params) {
  $returnValues = array();
  // generate custom data if ogm table or column does not exist
  if (!CRM_Core_DAO::checkTableExists('civicrm_value_domus_contribution_data')) {
    CRM_Ogmgenerator_DomusOgm::createCustomData();
  } else {
    if (!CRM_Core_DAO::checkFieldExists('civicrm_value_domus_contribution_data', 'domus_ogm')) {
      CRM_Ogmgenerator_DomusOgm::createCustomData();
    }
  }
  $sql = 'SELECT cont.id, cont.invoice_id, ogm.domus_ogm FROM civicrm_contribution cont
    LEFT JOIN civicrm_value_domus_contribution_data ogm ON cont.id = ogm.entity_id 
    WHERE cont.invoice_id IS NOT NULL AND ogm.domus_ogm IS NULL';
  $dao = CRM_Core_DAO::executeQuery($sql);
  while ($dao->fetch()) {
    $ogmNumber = CRM_Ogmgenerator_DomusOgm::generateOgm($dao->invoice_id);
    $countSql = 'SELECT COUNT(*) AS countOgm FROM civicrm_value_domus_contribution_data WHERE entity_id = %1';
    $countOgm = CRM_Core_DAO::singleValueQuery($countSql, array(1 => array($dao->id, 'Integer')));
    if ($countOgm == 0) {
      $ogmSql = 'INSERT INTO civicrm_value_domus_contribution_data (domus_ogm, entity_id) VALUES(%1, %2)';
    } else {
      $ogmSql = 'UPDATE civicrm_value_domus_contribution_data SET domus_ogm = %1 WHERE entity_id = %2';
    }
    CRM_Core_DAO::executeQuery($ogmSql, array(
      1 => array($ogmNumber, 'String'),
      2 => array($dao->id, 'Integer')
    ));
    $returnValues[] = 'OGM '.$ogmNumber.' generated for contribution '.$dao->id;
  }
  return civicrm_api3_create_success($returnValues, $params, 'DomusOgm', 'Generate');
}

