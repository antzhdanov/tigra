<?php

class Skit_Tigra_Adminhtml_MigrateController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->_title('Database migrations');
        $this->renderLayout();
    }

    /**
     * Update the DB softly unless the "force" parameter is set
     */
    public function updateAction() {
        $request = $this->getRequest();

        if ($name = $request->getParam('name')) {
            $status = $this->_applyMigrations(true, $name, $request->getPost('force', false));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Rollback migrations softly unless the "force" parameter is set
     */
    public function rollbackAction() {
        if ($name = $this->getRequest()->getParam('name')) {
            $status = $this->_applyMigrations(false, $name, $this->getRequest()->getPost('force', false));
        }

        if ($status) {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Prompt the user for the force update/downgrade
     */
    public function forceAction() {
        $request = $this->getRequest();
        $direction = $request->getParam('forward', false) ? 'update' : 'rollback';

        $this->loadLayout();
        $this->_title('Please confirm');
        $name = $request->getParam('name');
        $this->getLayout()
            ->getBlock('tigra')
            ->setName($name)
            ->setDirection($direction);

        $this->renderLayout();
    }

    protected function _applyMigrations($forward, $name, $force = false) {
        $db = Mage::getSingleton('tigra/db');
        $num = Mage::helper('tigra')->getNumByName($name);

        // apply migrations
        try {
            $status = $db->applyMigrations($forward, $num, $force);
        } catch (Exception $e) {
            // Missing migrations
            $this->_redirect('*/*/force', array('name' => $name, 'forward' => $forward));
            return false;
        }

        if ($status) {
            $msg = $forward ?
                        $this->__('The database was successfully upgraded.') :
                        $this->__('The database was successfully downgraded.');

            $this->_getSession()
                ->addSuccess($msg);
        } else {
            $this->_getSession()
                ->addError($this->__('The database was NOT updated. Check log for the details.'))
                ->addError(implode('<br/>', $db->getLog()));
        }

        return $status;
    }
}
