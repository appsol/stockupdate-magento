<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author stuart
 */
class AppSol_Stockupdate_Block_Adminhtml_Stockupdate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        Mage::helper('firephp')->send('AppSol_Stockupdate_Block_Adminhtml_Stockupdate_Edit_Tab_Form::_prepareForm');
        Mage::helper('firephp')->send(Mage::getSingleton('adminhtml/session')->getStockupdateData());
        if ($field_data = Mage::getSingleton('adminhtml/session')->getStockupdateData()) {
            $process_fieldset = $form->addFieldset('stockupdate_process', array(
                        'legend' => Mage::helper('stockupdate')->__('Assign Fields')
                    ));
            $options = array('-1' => 'Please Select...');
            if (is_array($field_data)) {
                foreach ($field_data as $key => $value) {
                    $options[$key] = $value;
                }
            }
            $process_fieldset->addField('stockupdate_sku', 'select', array(
                'label' => Mage::helper('stockupdate')->__('Assign SKU Field'),
                'name' => 'stockupdate_sku',
                'values' => $options,
                'required' => true
            ));

            $process_fieldset->addField('stockupdate_qty', 'select', array(
                'label' => Mage::helper('stockupdate')->__('Assign Qty Field'),
                'name' => 'stockupdate_qty',
                'values' => $options,
                'required' => true
            ));
            $options['-1'] = 'Ignore';
                $process_fieldset->addField('stockupdate_is_in_stock', 'select', array(
                'label' => Mage::helper('stockupdate')->__('Assign In Stock Field'),
                'name' => 'stockupdate_is_in_stock',
                'values' => $options,
                'value' => '-1'
            ));
            $process_fieldset->addField('stockupdate_price', 'select', array(
                'label' => Mage::helper('stockupdate')->__('Assign Price Field'),
                'name' => 'stockupdate_price',
                'values' => $options,
                'value' => '-1'
            ));
            $process_fieldset->addField('stockupdate_special', 'select', array(
                'label' => Mage::helper('stockupdate')->__('Assign Special Price Field'),
                'name' => 'stockupdate_special',
                'values' => $options,
                'value' => '-1'
            ));
            $process_fieldset->addField('stockupdate_ignore_first_row', 'checkbox', array(
                'label' => Mage::helper('stockupdate')->__('First Row contains Field Names'),
                'name' => 'stockupdate_ignore_first_row',
                'value' => 1,
                'checked' => false
            ));

            $form->setValues(Mage::getSingleton('adminhtml/session')->getStockupdateData());
            Mage::getSingleton('adminhtml/session')->setStockupdateData(null);
        } else {
            $upload_fieldset = $form->addFieldset('stockupdate_upload', array(
                        'legend' => Mage::helper('stockupdate')->__('Upload File')
                    ));

            $upload_fieldset->addField('stockupdate_delimiter', 'text', array(
                'label' => Mage::helper('stockupdate')->__('Field Delimiter'),
                'required' => true,
                'name' => 'stockupdate_delimiter',
                'value' => ','
            ));

            $upload_fieldset->addField('stockupdate_enclosure', 'text', array(
                'label' => Mage::helper('stockupdate')->__('Field Enclosure'),
                'required' => true,
                'name' => 'stockupdate_enclosure',
                'length' => '2',
                'value' => '"'
            ));

            $upload_fieldset->addField('stockupdate_csv', 'file', array(
                'label' => Mage::helper('stockupdate')->__('CSV File'),
                'required' => true,
                'name' => 'stockupdate_csv'
            ));
        }
        return parent::_prepareForm();
    }

}

?>
