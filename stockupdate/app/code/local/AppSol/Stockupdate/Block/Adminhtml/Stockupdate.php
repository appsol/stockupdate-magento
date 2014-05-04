<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * AppSol_Stockupdate_Block_Adminhtml_Upload
 *
 * @author stuart
 */
class AppSol_Stockupdate_Block_Adminhtml_Stockupdate extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
//        $this->_objectId = 'csvupload';
        $this->_controller = 'adminhtml_stockupdate';
        $this->_blockGroup = 'stockupdate';
        $this->_headerText = Mage::helper('stockupdate')->__('CSV Stock Update');
        $this->_addButtonLabel = Mage::helper('stockupdate')->__('Upload CSV');
        parent::__construct();
    }

}

?>
