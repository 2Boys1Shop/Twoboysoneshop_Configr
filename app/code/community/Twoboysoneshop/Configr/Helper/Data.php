<?php
class Twoboysoneshop_Configr_Helper_Data extends Mage_Adminhtml_Helper_Data
{
    public function isHistoryEnabled() 
    {
        return Mage::getStoreConfigFlag('configr/history/enabled');
    }
}