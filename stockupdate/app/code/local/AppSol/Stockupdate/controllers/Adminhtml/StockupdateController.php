<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StockupdateController
 *
 * @author stuart
 */
class AppSol_Stockupdate_Adminhtml_StockupdateController extends Mage_Adminhtml_Controller_Action {

    private $tempTableDb = null;
    private $tableName = 'stockupdate';

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('catalog/stockupdate')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Stock Update'), Mage::helper('adminhtml')->__('Process CSV'));
        return $this;
    }

    public function indexAction() {
        $this->_initAction();

        $this->_addContent($this->getLayout()
                        ->createBlock('stockupdate/adminhtml_stockupdate'));
        if (!$this->getRequest()->getParam('success')) {
            $this->setTempTableDb();
            $block = $this->getLayout()->createBlock('core/text', 'stockupdateIndex');
            $block->setText('<p>Upload a CSV file with Sku and Quantity columns. Optional In Stock, Price and Special Price columns may be included.</p>');
            $this->_addContent($block);
        }
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function editAction() {
        $this->_initAction();
        $stockupdateId = $this->getRequest()->getParam('id');
        $stockupdateModel = Mage::getModel('stockupdate/stockupdate')->load($stockupdateId);

        if ($stockupdateModel->getId() || $stockupdateId == 0) {
            Mage::register('stockupdate_data', $stockupdateModel);

            $this->loadLayout();
            $this->_setActiveMenu('catalog/stockupdate');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Stock Update'), Mage::helper('adminhtml')->__('Stock Update'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('CSV Upload'), Mage::helper('adminhtml')->__('CSV Upload'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('stockupdate/adminhtml_stockupdate_edit'))
                    ->_addLeft($this->getLayout()->createBlock('stockupdate/adminhtml_stockupdate_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('stockupdate')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            // Handle the uppload of the CSV file
            if (isset($_FILES['stockupdate_csv']['name']) && $_FILES['stockupdate_csv']['name'] != '') {
                $this->upload_StockCSV($data);
            } else {
                // Handle the field assignments from the form
                if (isset($data['stockupdate_sku'])) {
                    // Attempt to store the CSV data temporarily in the db table for easy handling
                    if ($this->storeTempData($data)) {
                        // Set a flag locally to show if the store is managing stock
                        $manage_stock = Mage::getStoreConfigFlag('cataloginventory/item_options/manage_stock');
                        // Fetch all the locally stored uploaded stock updates
                        $query = $this->tempTableDb->select()->from($this->tableName);
                        $stock_updates = $this->tempTableDb->fetchAll($query);
                        foreach ($stock_updates as $update) {
                            $savestock = false; // flag to show if the stock item needs saving
                            // Get the stock item and load it's config data
                            $stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($update['product_id']);
                            $stock_config = $stockitem->getData();
                            // Check if the Qty differs
                            if ($stock_config['qty'] != $update['qty']) {
                                $stockitem->setQty($update['qty']);
                                $savestock = true;
                            }
                            // Check if the is_in_stock flag differs
                            if ($update['is_in_stock'] > -1 && $update['is_in_stock'] != $stock_config['is_in_stock']) {
                                $stockitem->setData('is_in_stock', $update['is_in_stock']);
                                $savestock = true;
                            } elseif ($stock_config['qty'] != $update['qty']) {
                                $update['qty'] > 0 ? $stockitem->setData('is_in_stock', 1) : $stockitem->setData('is_in_stock', 0);
                                $savestock = true;
                            }
                            // If we have set new data then save the stock item
                            if ($savestock) {
                                $stockitem->setData('manage_stock', 1);
                                $stockitem->setData('use_config_manage_stock', $manage_stock ? 1 : 0);
                                $stockitem->save();
                            }
                            $saveproduct = false; // flag to show if the product needs saving
                            // Check if the price or special price fields have been set
                            if ($update['price'] > 0 || $update['special_price'] > 0) {
                                // Get the product and load it's config data
                                $product = Mage::getModel('catalog/product')->load($update['product_id']);
                                $product_config = $product->getData();
                                // Check if the price is set and differs from the current price
                                if ($update['price'] > 0 && $update['price'] != $product_config['price']) {
                                    $product->setPrice($update['price']);
                                    $saveproduct = true;
                                }
                                // Check if the special price is set and differs from the current special price
                                if ($update['special_price'] > 0 && $update['special_price'] != $product_config['special_price']) {
                                    $product->setSpecialPrice($update['special_price']);
                                    $saveproduct = true;
                                }
                                // If we have set new data then save the product
                                if ($saveproduct)
                                    $product->save();
                            }
                        }
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('stockupdate')->__('Stock data was successfully uploaded'));
                    }
                }

//                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('stockupdate')->__('No CSV File uploaded'));
                $this->_redirect('*/*/index', array('success' => 1));
            }
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function downloadAction() {
        Mage::log('AppSol_Stockupdate_Adminhtml_StockupdateController::downloadAction');
        $unknown_skus = Mage::getSingleton('adminhtml/session')->getStockupdateUnknownSkus();
        //set csv headers
        $this->getResponse()->setHeader('Content-Type', 'application/csv');
        $this->getResponse()->setHeader('Content-disposition', 'attachment; filename=unknown_skus.csv');
        $this->getResponse()->setHeader('Pragma', 'no-cache');
        $this->getResponse()->setHeader('Expires', '0');
        $csv_output = implode("\n", $unknown_skus);
        $this->getResponse()->setBody($csv_output);
    }

    private function upload_StockCSV($data) {
        $file_path = '';
        try {
            /* Starting upload */
            $uploader = new Varien_File_Uploader('stockupdate_csv');
            // Limit the extensions we will work with to 'csv'
            $uploader->setAllowedExtensions(array('csv'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            // Save the CSV file into the tmp directory
            $path = Mage::getBaseDir('var') . DS . 'tmp' . DS;
            $uploader->save($path, $_FILES['stockupdate_csv'] ['name']);
            $file_path = $path . $_FILES['stockupdate_csv'] ['name'];
        } catch (Exception $e) {
            
        }
        // If the CSV file uploaded successfully
        if (file_exists($file_path)) {
            // Parse the uploaded CSV file
            $data['stockupdate_csv'] = $_FILES['stockupdate_csv']['name'];
            $csv_parser = new Varien_File_Csv();
            $csv_data = $csv_parser->setDelimiter($data['stockupdate_delimiter'])->setEnclosure($data['stockupdate_enclosure'])->getData($file_path);
            if (count($csv_data)) {
                Mage::getSingleton('adminhtml/session')->setStockupdateData($csv_data[0]);
                Mage::getSingleton('adminhtml/session')->setStockupdateDelimiter($data['stockupdate_delimiter']);
                Mage::getSingleton('adminhtml/session')->setStockupdateEnclosure($data['stockupdate_enclosure']);
                Mage::getSingleton('adminhtml/session')->setStockupdateFile($_FILES['stockupdate_csv'] ['name']);
            }
            // Clear the form data
            Mage::getSingleton('adminhtml/session')->setFormData(false);
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('stockupdate')->__('No CSV File uploaded'));
        }
        $this->_redirect('*/*/edit');
    }

    /**
     * Store the data from the uploaded CSV file in the temp table
     * @param type $data
     * @return boolean 
     */
    private function storeTempData($data) {
        Mage::log('AppSol_Stockupdate_Adminhtml_StockupdateController::storeTempData');
        if (!$this->tempTableDb)
            $this->setTempTableDb();
        $file = Mage::getSingleton('adminhtml/session')->getStockupdateFile();
        $file_path = Mage::getBaseDir('var') . DS . 'tmp' . DS . $file;
        if (file_exists($file_path)) {
            $delimiter = Mage::getSingleton('adminhtml/session')->getStockupdateDelimiter();
            $enclosure = Mage::getSingleton('adminhtml/session')->getStockupdateEnclosure();
            $csv_parser = new Varien_File_Csv();
            $csv_data = $csv_parser->setDelimiter($delimiter)->setEnclosure($enclosure)->getData($file_path);
            Mage::getSingleton('adminhtml/session')->setStockupdateIsInStockCol($data['stockupdate_is_in_stock']);
            Mage::getSingleton('adminhtml/session')->setStockupdateSkuCol($data['stockupdate_sku']);
            Mage::getSingleton('adminhtml/session')->setStockupdateQtyCol($data['stockupdate_qty']);
            Mage::getSingleton('adminhtml/session')->setStockupdatePriceCol($data['stockupdate_price']);
            Mage::getSingleton('adminhtml/session')->setStockupdateSpecialCol($data['stockupdate_special']);
            $product_model = Mage::getModel('catalog/product');
            $store_id = Mage::app()->getStore()->getStoreId();
            $products = array();
            $unknown_skus = array();
            foreach ($csv_data as $i => $row) {
                if ((isset($data['stockupdate_ignore_first_row']) && $i == 0) || empty($row[$data['stockupdate_sku']]))
                    continue;

                if ($product_id = $product_model->getIdBySku($row[$data['stockupdate_sku']])) {
                    $in_stock = $data['stockupdate_is_in_stock'] == -1 ? -1 : $row[$data['stockupdate_is_in_stock']];
                    $sku = $row[$data['stockupdate_sku']];
                    $qty = $row[$data['stockupdate_qty']];
                    $price = $data['stockupdate_price'] == -1 ? 0.00 : $row[$data['stockupdate_price']];
                    $special_price = $data['stockupdate_special'] == -1 ? 0.00 : $row[$data['stockupdate_special']];
                    $products[] = '(' . $product_id . ',' . $store_id . ',"' . $sku . '",' . $qty . ',' . $in_stock . ',' . $price . ',' . $special_price . ')';
                } else {
                    $unknown_skus[] = $row[$data['stockupdate_sku']];
                }
            }
            if (count($unknown_skus)) {
                Mage::log($unknown_skus);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('stockupdate')->__('Some SKUs were not recognised. <a href="' . Mage::helper('adminhtml')->getUrl('stockupdate/adminhtml_stockupdate/download') . '">Download list</a>'));
                Mage::getSingleton('adminhtml/session')->setStockupdateUnknownSkus($unknown_skus);
            }
            if (count($products)) {
                $this->updateTempTableFromFile($products);
                unlink($file_path);
                return true;
            }
            return false;
        }
    }

    private function updateTempTableFromFile($products) {
        $sql = 'INSERT INTO `' . $this->tableName . '` (`product_id`, `store`, `sku`, `qty`, `is_in_stock`, `price`, `special_price`) VALUES ';
        foreach ($products as $product)
            $sql.= $product . ',';
        $sql = rtrim($sql, ',') . ';';
        $this->tempTableDb->query($sql);
    }

    private function updateStockQty($id, $qty, $is_in_stock) {
        $sql = "UPDATE cataloginventory_stock_item s_i, cataloginventory_stock_status s_s ";
        $sql.= "SET s_i.qty = '$qty', s_s.qty = '$qty', ";
        $sql.= $is_in_stock ?
                "s_i.is_in_stock = IF('$qty'>0, 1,0), s_s.stock_status = IF('$qty'>0, 1,0) " :
                "s_i.is_in_stock = 0, s_s.stock_status = 0 ";
        $sql.= "WHERE s_i.product_id = '$id' AND s_i.product_id = s_s.product_id";
        $this->tempTableDb->query($sql);
    }

    function updatePrice($product_id, $price) { // 60:price
        Mage::getModel('catalog/product')->load($product_id)->setPrice($price)->save();
//        $this->tempTableDb->query("UPDATE catalog_product_entity_decimal p_d
//               SET   p_d.value = '$price'
//               WHERE p_d.entity_id = '$id' AND p_d.attribute_id = '$attribute_id' ");
    }

    function updateSpecialPrice($product_id, $special_price) { // 61:special_price
        Mage::getModel('catalog/product')->load($product_id)->setSpecialPrice($special_price)->save();
//        $this->tempTableDb->query("UPDATE catalog_product_entity_decimal p_d
//               SET   p_d.value = '$special_price'
//               WHERE p_d.entity_id = '$id' AND p_d.attribute_id = '$attribute_id' ");
    }

    private function setTempTableDb() {
        $config = Mage::getConfig()->getResourceConnectionConfig('core_write');
        $dbConfig = array(
            'host' => $config->host,
            'username' => $config->username,
            'password' => $config->password,
            'dbname' => $config->dbname,
            'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8')
        );
        $this->tempTableDb = Zend_Db::factory('Pdo_Mysql', $dbConfig);
        $this->clearTempTable();
    }

    private function clearTempTable() {
        $sql = "TRUNCATE TABLE {$this->tableName};";
//        $sql = "DROP TABLE IF EXISTS {$this->tableName};
//        CREATE TABLE {$this->tableName} (
//            `stockupdate_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
//            `store` int(32) NOT NULL,
//            `sku` char(20) NOT NULL UNIQUE,
//            `qty` int(32) NOT NULL,
//            `is_in_stock` int(32) NOT NULL,
//            `price` DECIMAL(12,4),
//            `special_price` DECIMAL(12,4),
//            PRIMARY KEY (`stockupdate_id`)
//        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->tempTableDb->query($sql);
    }

}

?>
