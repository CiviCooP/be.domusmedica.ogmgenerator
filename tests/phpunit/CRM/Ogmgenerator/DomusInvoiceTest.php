<?php
/**
 *
 *  Tests for the function of the gestructureerd kenmerk generator
 *
 *  @author Klaas Eikelbooml (CiviCooP) <klaas.eikelboom@civicoop.org>
 *  @date 25-11-22 10:02
 *  @license AGPL-3.0
 *
 *
 */
class CRM_Ogmgenerator_DomusInvoiceTest extends \PHPUnit_Framework_TestCase  {

  public function testSimple(){
    // two test cases that were calculated wrong (ended in 00)
    $this->assertEquals(CRM_Ogmgenerator_DomusInvoice::generateDomusOgm('20172993'),'972017299397');
    $this->assertEquals(CRM_Ogmgenerator_DomusInvoice::generateDomusOgm('20173090'),'972017309097');
    // just some other testcases
    $this->assertEquals(CRM_Ogmgenerator_DomusInvoice::generateDomusOgm('20172481'),'972017248170');
    $this->assertEquals(CRM_Ogmgenerator_DomusInvoice::generateDomusOgm('20172482'),'972017248271');
  }
}