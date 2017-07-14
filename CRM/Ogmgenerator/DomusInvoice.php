<?php
class CRM_Ogmgenerator_DomusInvoice {

  private static function generateDomusId($optionValueName) {
    try {
      $domusInvoiceOptionValue = civicrm_api3('OptionValue', 'getsingle', array(
        'option_group' => 'domus_invoice',
        'name' => $optionValueName,
      ));
    } catch (CiviCRM_API3_Exception $ex) {
        $domusInvoiceOptionValue = self::createDomusMaxInvoiceOptionValue($optionValueName);
    }
    // first 4 digits is year. Check if year is still valid. If not, initialize for the year
    $nowDate = new DateTime();
    $nowYear = (int) $nowDate->format('Y');
    $maxYear = (int) substr($domusInvoiceOptionValue['value'], 0, 4);
    if ($nowYear > $maxYear) {
      $domusId = (string) $nowYear.'0001';
    } else {
      $base = (int) substr($domusInvoiceOptionValue['value'], 4,4);
      $newBase = str_pad(($base + 1), 4, '0', STR_PAD_LEFT);
      $domusId = (string) $maxYear.$newBase;
    }
      // now update max value in option value
    civicrm_api3('OptionValue', 'create', array(
      'id' => $domusInvoiceOptionValue['id'],
      'value' => $domusId,
    ));
    return $domusId;
  }
  /**
   * Method to generate Domus Medica Invoice ID
   *
   * @param int $contributionId
   * @return string $invoiceId
   */
  public static function generateDomusInvoiceId($contributionId) {
    // if contribution already has invoice use that
    try {
      $invoiceId = civicrm_api3('Contribution', 'getvalue', array('id' => $contributionId, 'return' => 'invoice_id'));
      if (!empty($invoiceId)&&(strlen($invoiceId)<20)) {
        return $invoiceId;
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    // else generate new invoice id
    $invoiceId = CRM_Ogmgenerator_DomusInvoice::generateDomusId('domus_max_invoice_id');
    // create and save ogm in custom field
    self::saveDomusOgm($contributionId, $invoiceId);
    return $invoiceId;
  }

  public static function generateDomusCreditNoteId() {
    return self::generateDomusId('domus_max_creditnote_id');
  }

  /**
   * create option group and option value for domus medica invoicing if required
   *
   * @return array
   */
  public static function createDomusMaxInvoiceOptionValue($optionValueName) {
    $optionGroupName = 'domus_invoice';
    // first check if the option group exists and if not, create
    $countOptionGroup = civicrm_api3('OptionGroup', 'getcount', array('name' => $optionGroupName));
    if ($countOptionGroup == 0) {
      civicrm_api3('OptionGroup', 'create', array(
        'name' => $optionGroupName,
        'title' => 'Domus Medica Faktuur Instellingen',
        'is_active' => 1,
        'is_reserved' => 1
      ));
      civicrm_api3('OptionValue', 'create', array(
        'option_group_id' => $optionGroupName,
        'name' => $optionValueName,
        'value' => '20170000',
        'is_active' => 1,
        'is_reserved' => 1
      ));
    } else {
      // check if option value exists and if not, create
      $countOptionValue = civicrm_api3('OptionValue', 'getcount', array(
        'option_group_id' => $optionGroupName,
        'name' => $optionValueName
      ));
      if ($countOptionValue == 0) {
        civicrm_api3('OptionValue', 'create', array(
          'option_group_id' => $optionGroupName,
          'name' => $optionValueName,
          'value' => '20170000',
          'is_active' => 1,
          'is_reserved' => 1
        ));
      }
    }
    // now return the option value array
    return civicrm_api3('OptionValue', 'getsingle', array(
      'option_group_id' => $optionGroupName,
      'name' => $optionValueName
    ));
  }

  /**
   * Method to generate custom group with custom fields if not exist yet
   *
   * @throws Exception when error creating custom group
   */
  public static function createCustomData() {
    $customGroupName = 'domus_contribution_data';
    // first get or create custom group
    $customGroupParams = array(
      'name' => $customGroupName,
      'extends' => 'Contribution',
      'table_name' => 'civicrm_value_' . $customGroupName
    );
    $countGroup = civicrm_api3('CustomGroup', 'getcount', $customGroupParams);
    if ($countGroup == 0) {
      $customGroupParams['title'] = 'DomusMedica OGM nummer voor Bijdrage';
      try {
        civicrm_api3('CustomGroup', 'create', $customGroupParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create custom group with name ' . $customGroupName . ' to extend Contribution in .'
          . __METHOD__ . '. Error from CiviCRM API CustomGroup create :' . $ex->getMessage());
      }
    }
    // now create custom field if not exists yet
    self::createCustomField($customGroupName);
  }

  /**
   * Method to create customField for ogm
   *
   * @param $customGroupName
   * @throws Exception when error creating custom field
   */
  private static function createCustomField($customGroupName) {
    $customOgmColumnName = 'domus_ogm';
    $customFieldParams = array(
      'custom_group_id' => $customGroupName,
      'name' => $customOgmColumnName,
      'column_name' => $customOgmColumnName);
    $countField = civicrm_api3('CustomField', 'getcount', $customFieldParams);
    if ($countField == 0) {
      $customFieldParams = array(
        'custom_group_id' => $customGroupName,
        'name' => $customOgmColumnName,
        'column_name' => $customOgmColumnName,
        'label' => 'OGM nummer',
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_active' => 1,
        'is_searchable' => 1,
        'is_view' => 1);
      try {
        civicrm_api3('CustomField', 'create', $customFieldParams);
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception('Could not create custom field domus_ogm in custom group' . $customGroupName . ' in .'
          . __METHOD__ . '. Error from CiviCRM API CustomField create :' . $ex->getMessage());
      }
    }
  }

  /**
   * Method to generate and save ogm
   *
   * @param $contributionId
   * @param $invoiceId
   */
  public static function saveDomusOgm($contributionId, $invoiceId) {
    self::checkCustomData();
    $ogmBase = '97'.$invoiceId;
    $ogmCheck = $invoiceId % 97;
    $ogmCheck = str_pad($ogmCheck, 2,"0",STR_PAD_LEFT);
    $domusOgm = $ogmBase.$ogmCheck;
    $countSql = 'SELECT COUNT(*) AS countOgm FROM civicrm_value_domus_contribution_data WHERE entity_id = %1';
    $countOgm = CRM_Core_DAO::singleValueQuery($countSql, array(1 => array($contributionId, 'Integer')));
    if ($countOgm == 0) {
      $ogmSql = 'INSERT INTO civicrm_value_domus_contribution_data (domus_ogm, entity_id) VALUES(%1, %2)';
    } else {
      $ogmSql = 'UPDATE civicrm_value_domus_contribution_data SET domus_ogm = %1 WHERE entity_id = %2';
    }
    CRM_Core_DAO::executeQuery($ogmSql, array(
      1 => array($domusOgm, 'String'),
      2 => array($contributionId, 'Integer')
    ));
  }

  /**
   * Method to disable custom field
   */
  public static function disableCustomField() {
    try {
      $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
        'custom_group_id' => 'domus_contribution_data',
        'name' => 'domus_ogm',
        'return' => 'id'
      ));
      civicrm_api3('CustomField', 'create', array(
        'id' => $customFieldId,
        'is_active' => 0
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to remove custom field
   */
  public static function removeCustomField() {
    try {
      $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
        'custom_group_id' => 'domus_contribution_data',
        'name' => 'domus_ogm',
        'return' => 'id'
      ));
      civicrm_api3('CustomField', 'delete', array('id' => $customFieldId));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to check if custom data exist and create if not
   */
  public static function checkCustomData() {
// generate custom data if ogm table or column does not exist
    if (!CRM_Core_DAO::checkTableExists('civicrm_value_domus_contribution_data')) {
      self::createCustomData();
    } else {
      if (!CRM_Core_DAO::checkFieldExists('civicrm_value_domus_contribution_data', 'domus_ogm')) {
        self::createCustomData();
      }
    }
  }
}
