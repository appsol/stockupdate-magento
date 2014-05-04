<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * AppSol_Stockupdate_Block_Adminhtml_Process
 *
 * @author stuart
 */
class AppSol_Stockupdate_Block_Adminhtml_Stockupdate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'csvprocess';
        $this->_controller = 'adminhtml_stockupdate';
        $this->_blockGroup = 'stockupdate';
        $this->_updateButton('save', 'label', Mage::helper('stockupdate')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('stockupdate')->__('Delete Item'));
    }
    
    public function getHeaderText() {
        if( Mage::registry('stockupdate_data') && Mage::registry('stockupdate_data')->getId() ) {
            return Mage::helper('stockupdate')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('stockupdate_data')->getTitle()));
        } else {
            return Mage::helper('stockupdate')->__('Add Item');
        }
    }
}
?>
