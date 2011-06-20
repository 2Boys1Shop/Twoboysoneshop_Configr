<?php

class Twoboysoneshop_Configr_Block_Overview_Edit extends Mage_Adminhtml_Block_System_Config_Edit
{

    protected function _prepareLayout()
    {
        $ret = parent::_prepareLayout();
        
        $this->setTemplate('twoboysoneshop_configr/overview/edit.phtml');
        
        $this->unsetChild('save_button');
        
        $this->setFormVarName('form' . uniqid());
        
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Save Config'),
                    'onclick'   => 'window.' . $this->getFormVarName() . '.submit()',
                    'class' => 'save',
                ))
        );
        
        return $ret;
    }
    
    public function getSaveUrl()
    {
        $params = array(
            'section' => $this->getSection(),
            'website' => $this->getWebsite(),
        );
        if ($this->getStore()) {
            $params['store'] = $this->getStore();
        }
        return $this->getUrl('*/*/save', $params);
    }
}
