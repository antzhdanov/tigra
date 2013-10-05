<?php

/**
 * Renders the column based on a callback property
 *
 * Callback function should take the $row as parameter and return the index
 * of the element in "actions" array
 *
 * @category    Skit
 * @package     Skit_Tigra
 * @author      Anton Zhdanov <azhdanov@terricone.com>
 */
class Skit_Tigra_Block_Adminhtml_Grid_Column_Renderer_Action_Conditional
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $actions = $this->getColumn()->getActions();

        try {
            $index = call_user_func($this->getColumn()->getCallback(), $row);
            return $this->_toLinkHtml($actions[$index], $row);
        } catch (Exception $e) {
            Mage::logException($e);
            return '';
        }
    }
}
