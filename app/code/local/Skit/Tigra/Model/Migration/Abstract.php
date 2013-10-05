<?php

class Tigra_Migration {
    protected function _getSetup() {
        return Mage::getModel('eav/entity_setup', 'core_setup');
    }

    protected function _sql($sql)
    {
        $this->_getSetup()->run($sql);
    }
}
