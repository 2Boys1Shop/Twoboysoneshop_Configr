<?php

class Twoboysoneshop_Configr_Block_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('configrHistoryGrid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('configr/history_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('history_id', array(
            'header'    => Mage::helper('adminhtml')->__('ID'),
            'width'     => 5,
            'align'     => 'right',
            'sortable'  => true,
            'index'     => 'history_id',
        ));

        $this->addColumn('scope', array(
            'header'    => Mage::helper('adminhtml')->__('Scope'),
            'index'     => 'scope',
            'width'     => '50px',
        ));

        $this->addColumn('scope_id', array(
            'header'    => Mage::helper('configr')->__('Scope ID'),
            'index'     => 'scope_id',
            'width'     => '50px',
        ));

        $this->addColumn('path', array(
            'header'    => Mage::helper('adminhtml')->__('Path'),
            'index'     => 'path',
        ));

        $this->addColumn('old_value', array(
            'header'    => Mage::helper('configr')->__('Old Value'),
            'index'     => 'old_value',
        ));

        $this->addColumn('value', array(
            'header'    => Mage::helper('configr')->__('New Value'),
            'index'     => 'value',
        ));

        $this->addColumn('user_name', array(
            'header'    => Mage::helper('adminhtml')->__('User'),
            'index'     => 'user_name',
            'width'     => '150px',
        ));
        
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Updated At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '100px',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('configr/history/edit', array('history_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('configr/history/historyGrid', array());
    }

}
