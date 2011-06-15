<?php

class Twoboysoneshop_Configr_Block_History_Edit_Tab_Detail extends Mage_Adminhtml_Block_Template
{

    protected function _construct()
    {
        $this->setTemplate('twoboysoneshop_configr/history/detail.phtml');
        $this->assign('_history', Mage::registry('history_entry'));
        parent::_construct();
    }
   
}
