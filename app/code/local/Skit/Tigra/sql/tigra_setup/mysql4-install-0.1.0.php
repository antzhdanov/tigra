<?php

$installer = $this;
$installer->startSetup();
$installer->run("

CREATE TABLE IF NOT EXISTS {$installer->getTable('tigra/migration')} (
    `change_number` bigint(20) NOT NULL,
    `start_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `complete_dt` timestamp NULL DEFAULT NULL,
    `description` SMALLTEXT NOT NULL,
    PRIMARY KEY (`change_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

");

$installer->_conn->insert(
    $this->getTable('core_config_data'),
    array(
        'value' => Mage::getBaseDir() . DS . 'migrations',
        'path' => 'dev/tigra/path'
    )
);

$installer->endSetup();
