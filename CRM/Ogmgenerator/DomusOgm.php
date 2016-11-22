<?php

/**
 * Class for Domus Medica OGM generation
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date Nov 2016
 * @license AGPL-3.0
 */
class CRM_Ogmgenerator_DomusOgm {

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
   * EMethod to create customField for ogm
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
   * Method to generate the OGM for an incoming invoice number
   * @param $invoiceId
   * @return string
   */
  public static function generateOgm($invoiceId) {
    $leadingZeros = 8 - strlen($invoiceId);
    for ($i = 1; $i <= $leadingZeros; $i++) {
      $invoiceId = '0'.$invoiceId;
    }
    $ogmBase = '97'.$invoiceId;
    $ogmCheck = $invoiceId % 97;
    return $ogmBase.$ogmCheck;
  }
}