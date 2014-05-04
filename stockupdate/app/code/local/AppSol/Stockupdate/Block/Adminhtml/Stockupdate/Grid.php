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
class AppSol_Stockupdate_Block_Adminhtml_Stockupdate_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('stockupdateResults');
        $this->setDefaultSort('sku');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('stockupdate/stockupdate')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('stockupdate')->__('ID'),
            'align' => 'right',
            'width' => '10px',
            'index' => 'stockupdate_id'
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('stockupdate')->__('SKU'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'sku'
        ));
        $this->addColumn('qty', array(
            'header' => Mage::helper('stockupdate')->__('Qty'),
            'align' => 'right',
            'width' => '10px',
            'index' => 'qty'
        ));
        if (Mage::getSingleton('adminhtml/session')->getStockupdateIsInStockCol() != -1) {
            $this->addColumn('instock', array(
                'header' => Mage::helper('stockupdate')->__('In Stock'),
                'align' => 'right',
                'width' => '10px',
                'index' => 'is_in_stock'
            ));
        }
        if (Mage::getSingleton('adminhtml/session')->getStockupdatePriceCol() != -1) {
            $this->addColumn('price', array(
                'header' => Mage::helper('stockupdate')->__('Price'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'price'
            ));
        }
        if (Mage::getSingleton('adminhtml/session')->getStockupdateSpecialCol() != -1) {
            $this->addColumn('special', array(
                'header' => Mage::helper('stockupdate')->__('Special Price'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'special_price'
            ));
        }
        return parent::_prepareColumns();
    }

}

?>
