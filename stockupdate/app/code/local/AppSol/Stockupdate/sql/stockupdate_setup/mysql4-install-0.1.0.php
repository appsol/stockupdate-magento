<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$installer = $this;
$installer->startSetup();
$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('stockupdate')};
        CREATE TABLE {$this->getTable('stockupdate')} (
            `stockupdate_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `store` int(32) NOT NULL,
            `sku` char(20) NOT NULL UNIQUE,
            `qty` int(32) NOT NULL,
            `is_in_stock` int(32) NOT NULL,
            `price` DECIMAL(12,4),
            `special_price` DECIMAL(12,4),
            PRIMARY KEY (`stockupdate_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
    );
$installer->endSetup();
?>
