<?php

class CRM_Ogmgenerator_Page_DomusInvFinType extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Domus Medica Factuur nummers voor Financial Types'));
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/civicrm/domusinvfintypelist', 'reset=1', true));
    $domusInvFinType = new CRM_Ogmgenerator_BAO_DomusInvFinType();
    $domusInvFinType->updateAll();
    $rows = $domusInvFinType->getValues(array());
    foreach ($rows as $rowId => $row) {
      $rows[$rowId]['financial_type_label'] = civicrm_api3('FinancialType', 'getvalue', array(
        'id' => $row['financial_type_id'],
        'return' => 'name'
      ));
    }
    $this->assign('domus_inv_fin_types', $rows);
    parent::run();
  }
}
