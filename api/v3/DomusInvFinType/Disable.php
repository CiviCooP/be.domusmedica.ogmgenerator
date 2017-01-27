<?php

/**
 * DomusInvFinType.Disable API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_domus_inv_fin_type_Disable_spec(&$spec) {
  $spec['id']['api.required'] = 1;
}

/**
 * DomusInvFinType.Disable API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_domus_inv_fin_type_Disable($params) {
  $returnValues = array();
  $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
  $domusInvFinType->disable($params['id']);
  return civicrm_api3_create_success($returnValues, $params, 'DomisInvFinType', 'disable');
}
