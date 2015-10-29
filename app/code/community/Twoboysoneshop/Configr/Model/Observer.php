<?php
class Twoboysoneshop_Configr_Model_Observer
{
    public function logConfigDataChange($observer) 
    {
        $model = $observer->getObject();
        if ($model instanceof Mage_Core_Model_Config_Data) {
            if (!Mage::helper('configr')->isHistoryEnabled()) {
                return $this;
            }
            
            if ($model->isValueChanged()) {
                $oldValue = $model->getOldValue();
                
                $history = Mage::getModel('configr/history');
                $history->setData(array(
                    'scope'      => $model->getScope(),
                    'scope_id'   => $model->getScopeId(),
                    'path'       => $model->getPath(),
                    'old_value'  => $model->getOldValue(),
                    'value'      => $model->getValue(),
                    'user_id'    => Mage::getSingleton('admin/session')->getUser()->getId(),
                    'user_name'  => Mage::getSingleton('admin/session')->getUser()->getUsername(),
                    'created_at' => Mage::getModel('core/date')->gmtDate(),
                ))->save();
            }
        }
    }
    
    public function redirectToConfigOverview($observer) 
    {
        $controller = $observer->getControllerAction();
        
        $session = Mage::getSingleton('adminhtml/session');
        
        if ($session->getRedirectToConfigOverview(true)) {
            $controller->getResponse()->setRedirect(Mage::getUrl('adminhtml/configr_overview/index', array()));
        }
    }
}
