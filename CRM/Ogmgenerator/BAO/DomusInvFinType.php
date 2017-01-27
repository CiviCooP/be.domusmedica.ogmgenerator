<?php
/**
 * Class BAO for Domus Medica Invoicing per Financial Type
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 23 Jan 2017
 * @license AGPL-3.0
 */

class CRM_Ogmgenerator_BAO_DomusInvFinType extends CRM_Ogmgenerator_DAO_DomusInvFinType {
  /**
   * Method to add a domus invoice financial type
   *
   * @param array $params
   * @throws Exception when invalid array params
   * @return array $result
   */
  public function add($params) {
    $result = array();
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a domus invoice financial type');
    }
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $domusInvFinType->$key = $value;
      }
    }
    $domusInvFinType->save();
    self::storeValues($domusInvFinType, $result);
    return $result;
  }

  /**
   * Method to get domus invoice financial types
   *
   * @param array $params
   * @return array $result
   */
  public function getValues($params) {
    $result = array();
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $domusInvFinType->$key = $value;
        }
      }
    }
    $domusInvFinType->find();
    while ($domusInvFinType->fetch()) {
      $row = array();
      self::storeValues($domusInvFinType, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Method to enable the domus invoice numbering for financial type
   *
   * @param int $domusInvFinTypeId
   * @throws Exception when empty domusInvFinTypeId
   */
  public function enable($domusInvFinTypeId) {
    if (empty($domusInvFinTypeId)) {
      throw new Exception('Domus Medica invoice financial type id can not be empty when attempting to enable domus invoice numbering');
    }
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    $domusInvFinType->id = $domusInvFinTypeId;
    $domusInvFinType->find(true);
    self::add(array('id' => $domusInvFinType->id, 'is_domus_invoice' => 1));
  }

  /**
   * Method to disable the domus invoice numbering for financial type
   *
   * @param int $domusInvFinTypeId
   * @throws Exception when empty domusInvFinTypeId
   */
  public function disable ($domusInvFinTypeId) {
    if (empty($domusInvFinTypeId)) {
      throw new Exception('Domus Medica invoice financial type id can not be empty when attempting to enable domus invoice numbering');
    }
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    $domusInvFinType->id = $domusInvFinTypeId;
    $domusInvFinType->find(true);
    self::add(array('id' => $domusInvFinType->id, 'is_domus_invoice' => 0));

  }

  /**
   * Method to determine if fin type already exists in domus_inv_fin_type
   *
   * @param int financialTypeId
   * @return bool
   */
  public function financialTypeExists($financialTypeId) {
    if (empty($financialTypeId)) {
      return FALSE;
    }
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    $domusInvFinType->financial_type_id = $financialTypeId;
    if ($domusInvFinType->count() == 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * Method to update the domus_inv_fin_type table with all current financial types
   */
  public function updateAll() {
    // add new financial types
    $financialTypes = civicrm_api3('FinancialType', 'get', array('options' => array('limit' => 0)));
    foreach ($financialTypes['values'] as $financialType) {
      $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
      if (!$domusInvFinType->financialTypeExists($financialType['id'])) {
        $domusInvFinType->add(array(
          'financial_type_id' => $financialType['id'],
          'is_domus_invoice' => 0));
      }
    }
    // now remove the records with financial types that no longer exist in civicrm
    $sql = "SELECT id FROM domus_inv_fin_type 
WHERE financial_type_id NOT IN (SELECT DISTINCT(id) FROM civicrm_financial_type)";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $domusInvFinType->id = $dao->id;
      $domusInvFinType->find();
      if ($domusInvFinType->fetch()) {
        $domusInvFinType->delete();
      }
    }
  }

  /**
   * Method to determine if domus invoicing should be used for financial type
   *
   * @param int $financialTypeId
   * @return bool
   */
  public static function hasDomusInvoicing($financialTypeId) {
    if (!empty($financialTypeId)) {
      $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
      $domusInvFinType->financial_type_id = $financialTypeId;
      $domusInvFinType->find();
      if ($domusInvFinType->fetch()) {
        if ($domusInvFinType->is_domus_invoice == 1) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}