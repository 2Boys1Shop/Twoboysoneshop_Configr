<?php

class Twoboysoneshop_Configr_Block_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'configr';
        $this->_controller = 'history';
        $this->_headerText = Mage::helper('adminhtml')->__('History');
        $this->_addButtonLabel = false;
        parent::__construct();
        $this->_removeButton('add');
    }

}
