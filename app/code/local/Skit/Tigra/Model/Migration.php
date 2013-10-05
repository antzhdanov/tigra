<?php

class Skit_Tigra_Model_Migration extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('tigra/migration');
    }

    protected function _afterLoad() {
        parent::_afterLoad();

        if ($this->getBasename()) {
            $info = Mage::helper('tigra')->getInfoByName($this->getBasename());

            $this->addData($info);
        }

        return $this;
    }

    public function getLastApplied() {
        if (!$this->_last)
            $this->_last = $this->getResource()->getLast();

        return $this->_last;
    }

    public function isApplied() {
        return $this->getLastApplied() > $this->getNum();
    }
}
