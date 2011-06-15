<?php

class Twoboysoneshop_Configr_Block_History_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('configr')->__('History Details'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('configr')->__('Information'),
            'title'     => Mage::helper('configr')->__('Information'),
            'content'   => $this->getLayout()->createBlock('configr/history_edit_tab_detail')->toHtml(),
            'active'    => true
        ));

//        $this->addTab('roles_section', array(
//            'label'     => Mage::helper('adminhtml')->__('User Role'),
//            'title'     => Mage::helper('adminhtml')->__('User Role'),
//            'content'   => $this->getLayout()->createBlock('adminhtml/permissions_user_edit_tab_roles', 'user.roles.grid')->toHtml(),
//        ));
        return parent::_beforeToHtml();
    }

}
