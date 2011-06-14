<?php
class Twoboysoneshop_Configr_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('configr/history', 'history_id');
    }
}