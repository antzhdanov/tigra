<?php

/**
 * Migrations adminhtml grid
 *
 * @category    Skit_Tigra
 * @package     Skit_Tigra
 * @author      Anton Zhdanov <azhdanov@terricone.com>
 */
class Skit_Tigra_Block_Adminhtml_Migration_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();

        $this->setId('migrationsGrid')
            ->setDefaultSort('id')
            ->setDefaultDir('ASC');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('tigra/migration_file_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('filename', array(
            'header'    => $this->__('Number'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'num'
        ));

        $this->addColumn('description', array(
            'header'    => $this->__('Description'),
            'align'     =>'left',
            'index'     => 'description'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('core')->__('Action'),
                'width'     => '150px',
                'type'      => 'action',
                'getter'    => 'getBasename',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('Update to'),
                        'url'     => array('base'=>'*/adminhtml_migrate/update'),
                        'field'   => 'name'
                    ),
                    array(
                        'caption' => Mage::helper('sales')->__('Rollback (including)'),
                        'url'     => array('base'=>'*/adminhtml_migrate/rollback'),
                        'field'   => 'name'
                    )
                ),
                'filter'    => false,
                'renderer'  => 'tigra/adminhtml_grid_column_renderer_action_conditional',
                'callback'  => function($row) {
                                    return (int)($row['num'] <= Mage::getSingleton('tigra/db')
                                                            ->getLastApplied());
                                },
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true
        ));

        return parent::_prepareColumns();
    }
}
