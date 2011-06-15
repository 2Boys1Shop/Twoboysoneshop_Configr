<?php

class Twoboysoneshop_Configr_Block_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'history_id';
        $this->_blockGroup = 'configr';
        $this->_controller = 'history';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        
        $this->_addButton('restore', array(
            'label'     => Mage::helper('configr')->__('Restore Old Value'),
            'onclick'   => 'historyForm.submit();',
            'class'     => 'save',
        ), 1);
    }

    public function getHeaderText()
    {
        if (Mage::registry('history_entry')->getId()) {
            return Mage::helper('configr')->__("View History ID #%s", $this->htmlEscape(Mage::registry('history_entry')->getId()));
        }
    }

}
