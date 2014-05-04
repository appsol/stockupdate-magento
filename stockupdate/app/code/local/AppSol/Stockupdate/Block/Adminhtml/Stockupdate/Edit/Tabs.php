<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tabs
 *
 * @author stuart
 */
class AppSol_Stockupdate_Block_Adminhtml_Stockupdate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    
    public function __construct() {
        parent::__construct();
        $this->setId('stockupdate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('stockupdate')->__('Process CSV'));
    }
    
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('stockupdate')->__('Assign Fields'),
            'title' => Mage::helper('stockupdate')->__('Assign Fields'),
            'content' => $this->getLayout()->createBlock('stockupdate/adminhtml_stockupdate_edit_tab_form')->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}

?>
