<?php

class Skit_Tigra_Model_Mysql4_Migration extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('tigra/migration', 'change_number');
        $this->_isPkAutoIncrement = false;
    }

    /**
     * Tries to fetch the latest applied migration number. If no migrations were
     * applied, returns 0
     *
     * @return integer
     */
    public function getLast() {
        $last = $this->_getWriteAdapter()
            ->query('SELECT MAX(' . $this->getIdFieldName() . ') FROM '. $this->getMainTable())
            ->fetchColumn();

        return $last ?: 0;
    }
}
