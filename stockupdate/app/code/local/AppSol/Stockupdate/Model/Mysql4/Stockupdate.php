<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stockupdate
 *
 * @author stuart
 */
class AppSol_Stockupdate_Model_Mysql4_Stockupdate extends Mage_Core_Model_Mysql4_Abstract {
    
    public function _construct() {
        $this->_init('stockupdate/stockupdate', 'stockupdate_id');
    }
}

?>
