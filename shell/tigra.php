<?php

require_once 'abstract.php';

class Tigra extends Mage_Shell_Abstract {
    public function _construct() {
        // register fatal error handlers
        register_shutdown_function(function() {
            if (error_get_last() && class_exists('Mage')) {
                echo "\nRolling back all changes after fatal error.\n";
                Mage::getSingleton('core/resource')->getConnection('core_write')->rollback();
            }
        });
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  tigra -- [options]
Database versioning system for Magento.

  --to <migration number>       Update database to specified migration (inclusive) (works with "up" and "down")
  --generate <name>             Generate skeleton migration
  up                            Upgrade database
  down                          Downgrade database
  version                       Show the current DB version
  help                          This help

USAGE;
    }

    /**
     * The main method - run application
     */
    public function run() {
        try {
            if ($this->getArg('up')) {
                // load migrations from files
                Mage::getSingleton('tigra/db')
                    ->setIsCli(true)
                    ->applyMigrations(true, $this->getArg('to'));
            } else if ($this->getArg('down')) {
                Mage::getSingleton('tigra/db')
                    ->setIsCli(true)
                    ->applyMigrations(false, $this->getArg('to'));
            } else if ($name = $this->getArg('generate')) {
                Mage::getSingleton('tigra/db')
                    ->setIsCli(true)
                    ->generate($name);
            } else if ($this->getArg('version')) {
                Mage::getSingleton('tigra/db')
                    ->setIsCli(true)
                    ->formatLastApplied();
            } else {
                echo $this->usageHelp();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

$tigra = new Tigra();
$tigra->run();
