<?php

/**
 * Migrations grid container
 *
 * @category    Skit
 * @package     Skit_Tigra
 * @author      Anton Zhdanov <azhdanov@terricone.com>
 */
class Skit_Tigra_Block_Adminhtml_Migration extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        parent::__construct();

        $this->_controller = 'adminhtml_migration';
        $this->_blockGroup = 'tigra';
        $this->_headerText = $this->__('Manage Migrations');
        $this->_removeButton('add');
    }
}
