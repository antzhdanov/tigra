<?php

class Tigra_Migration {
    /**
     * Setup resource - used for a bunch of maintenance stuff
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    protected function _getSetup() {
        return Mage::getModel('eav/entity_setup', 'core_setup');
    }

    /**
     * Execute SQL
     */
    protected function _sql($sql) {
        $this->_getSetup()->run($sql);
    }
}
