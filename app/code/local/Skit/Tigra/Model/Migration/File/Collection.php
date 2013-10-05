<?php

class Skit_Tigra_Model_Migration_File_Collection extends Varien_Data_Collection_Filesystem {
    public function __construct() {
        // load config
        $dir = Mage::getStoreConfig('dev/tigra/path');

        if (!$dir) {
            $dir = Mage::getBaseDir() . DS . 'migrations';
        }

        $this->addTargetDir($dir)
            ->setFilesFilter('/^[0-9]+_[a-z0-9\.-_]+?\.php$/i');
    }

    public function addItem(Varien_Object $item) {
        $info = Mage::helper('tigra')->getInfoByName($item->getBasename());

        $item->addData($info);

        return parent::addItem($item);
    }

    public function hasItem($name) {
        foreach ($this->getItems() as $item) {
            if ($item->getDescription() == strtolower($name))
                return true;
        }

        return false;
    }

    /**
     * Ensures that the collection would contain only not-applied migrations
     *
     * @param integer Migration number to stop on
     */
    public function addNotAppliedFilter($to = false) {
        return $this->_addNumFilter(array(Mage::getModel('tigra/db')->getLastApplied(), $to));
    }

    /**
     * Ensures that the collection would contain only applied migrations
     *
     * @param integer Migration number to stop on
     */
    public function addAppliedFilter($to = false) {
        $this->setOrder('basename')
            ->_addNumFilter(array($to - 1, Mage::getModel('tigra/db')->getLastApplied()));

        return $this;
    }

    /**
     * Checks is there are missing migrations in the chain
     *
     * @param boolean Upgrade or downgrade?
     * @param integer
     *
     * @return boolean
     */
    public function hasMissingItems($forward = true, $from = 1) {
        $counter = $forward ? $from : Mage::getModel('tigra/db')->getLastApplied();

        foreach ($this->getItems() as $migration) {
            if ((int)$migration->getNum() != $counter)
                return true;

            $counter += ($forward ? 1 : -1);
        }

        return false;
    }

    /**
     * @param array [from, to]
     */
    protected function _addNumFilter($range) {
        $this->addCallbackFilter(
            'basename',
            $range,
            'and',
            function ($field, $range, $row) {
                $info = Mage::helper('tigra')->getInfoByName($row[$field]);

                return $range[1] ?
                    (int)$info['num'] > $range[0] && (int)$info['num'] <= $range[1] :
                    (int)$info['num'] > $range[0];
            });

        return $this;
    }
}
