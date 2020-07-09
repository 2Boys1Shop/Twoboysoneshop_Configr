<?php
class Twoboysoneshop_Configr_Configr_OverviewController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/acl')
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('Configuration'), $this->__('Configuration'))
            ->_addBreadcrumb($this->__('Overview'), $this->__('Overview'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Configuration'))
             ->_title($this->__('Overview'));
    
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections     = $configFields->getSections()->asArray();
        $tabs         = $configFields->getTabs()->asArray();
        $stores       = Mage::app()->getStores(true);
        $websites     = Mage::app()->getWebsites(true);
        $websitesCollection = Mage::app()->getWebsites(true);

        uasort($sections, array($this, '_sortByOrder'));
        uasort($tabs, array($this, '_sortByOrder'));
        ksort($stores);

        $checkAcl = Mage::getStoreConfigFlag('configr/acl/enabled');
        
        // Add sections to corresponding tabs
        foreach ($sections as $sectionKey => $section) {
            // Show only allowed sections if acl check is enabled
            if (!$checkAcl || Mage::getSingleton('admin/session')->isAllowed('system/config/' . $sectionKey)) {
                $tabs[$section['tab']]['sections'][$sectionKey] = $section;
            }
        }

        // Convert websites to arrays
        foreach ($websites as $websiteKey => $website) {
            $websites[$websiteKey] = $website->getData();
        }

        $comparedStores = array();
        $comparedWebsites = array();

        $comparedStores[0]['name'] = 'default';

        // Convert stores to arrays
        $storeKeys = $this->getRequest()->getParam('stores') ? $this->getRequest()->getParam('stores') : array();
        foreach ($stores as $storeKey => $store) {
            if (0 == $storeKey || in_array($store->getCode(), $storeKeys)) {
                $comparedStores[$storeKey] = $store->getData();
            }
            $stores[$storeKey] = $store->getData();
            $websites[$store['website_id']]['stores'][$storeKey] = $store->getData();
        }

        foreach ($tabs as &$tab) {
            if (empty($tab['sections'])) {
                continue;
            }
            
            foreach ($tab['sections'] as $sectionKey => &$section) {

                if (empty($section['groups'])) {
                    $section['groups'] = array();
                    continue;
                }
                uasort($section['groups'], array($this, '_sortByOrder'));

                foreach ($section['groups'] as $groupKey => &$group) {

                    if (empty($group['label'])) {
                        $group['label'] = '---';
                    }
                    if (empty($group['fields'])) {
                        $group['fields'] = array();
                        continue;
                    }
                    uasort($group['fields'], array($this, '_sortByOrder'));

                    foreach ($group['fields'] as $fieldKey => &$field) {
                        if (empty($field) || !is_array($field)) {
                            $field = array();
                        }
                        if (empty($field['label'])) {
                            $field['label'] = $fieldKey;
                        }

                        // Store values of the current config_key
                        $configKey = $sectionKey . '/' . $groupKey . '/' . $fieldKey;
                        $field['config_key'] = $configKey;
                        $field['stores'] = array();

                        foreach ($comparedStores as $storeId => $store) {
                            $field['stores'][$storeId] = Mage::getStoreConfig($configKey, $storeId);
                        }
                        
                        foreach ($websitesCollection as $websiteId => $website) {
                            $field['websites'][$websiteId] = $website->getConfig($configKey);
                        }
                    }
                }
            }
        }

        $this->_initAction();
        $this->getLayout()->getBlock('configr.overview.index')
                          ->addData(array(
                              'tabs'            => $tabs,
                              'stores'          => $stores,
                              'compared_stores' => $comparedStores,
                              'websites'        => $websites,
                          ));
        $this->renderLayout();
    }

    public function editAction()
    {
        $configKey = $this->getRequest()->getPost('configKey');
        $storeId   = $this->getRequest()->getPost('storeId');
        
        list($section, $group, $field) = explode('/', $configKey);
        
        $fieldSetId = $section . '_' . $group;
        $elementId  = $section . '_' . $group . '_' . $field;
        
        
        
        $store   = Mage::app()->getStore($storeId);
        $website = $store->getWebsite();
        
        $this->getRequest()->setParam('section', $section);
        
        $this->loadLayout();
        $body = '';
        
        // Website Block
        $this->getRequest()->setParam('website', $website->getCode());
        
        $editBlock = $this->getLayout()->createBlock('configr/overview_edit')->initForm();
        $editBlock->setTitle($this->__('Website'))
                  ->setSection($section)
                  ->setWebsite($website->getCode());
        
        $form = $editBlock->getChild('form');
        $form = $form->getForm();
        $fieldsetCollection = $form->getElements();
        foreach ($fieldsetCollection as $fieldset) {
            if ($fieldset->getId() != $fieldSetId) {
                $fieldsetCollection->remove($fieldset->getId());
                continue;
            }
            $elementCollection = $fieldset->getElements();
            foreach ($elementCollection as $element) {
                if ($element->getId() != $elementId) {
                    $elementCollection->remove($element->getId());
                    continue;
                }
            }
        }
        
        $body .= $editBlock->toHtml();
        
        
        
        // Store Block
        $this->getRequest()->setParam('store', $store->getCode());
        
        $editBlock = $this->getLayout()->createBlock('configr/overview_edit')->initForm();
        $editBlock->setTitle($this->__('StoreView'))
                  ->setSection($section)
                  ->setWebsite($website->getCode())
                  ->setStore($store->getCode());
        $form = $editBlock->getChild('form');
        $form = $form->getForm();
        $fieldsetCollection = $form->getElements();
        foreach ($fieldsetCollection as $fieldset) {
            if ($fieldset->getId() != $fieldSetId) {
                $fieldsetCollection->remove($fieldset->getId());
                continue;
            }
            $elementCollection = $fieldset->getElements();
            foreach ($elementCollection as $element) {
                if ($element->getId() != $elementId) {
                    $elementCollection->remove($element->getId());
                    continue;
                }
            }
        }
        $body .= $editBlock->toHtml();
        
        // JS
        $body .= $this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/shipping/ups.phtml')->toHtml();
        $body .= $this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/config/js.phtml')->toHtml();
        $body .= $this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/shipping/applicable_country.phtml')->toHtml();

        
        
        $this->getResponse()->setBody($body);
    }

    public function saveAction() 
    {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setRedirectToConfigOverview(true);

        $this->_forward('save', 'system_config', 'admin');
    }

    protected function _sortByOrder($a, $b)
    {
        if (!is_array($a) || !is_array($b)) {
            return 0;
        }
        if (!array_key_exists('sort_order', $a)) {
            $a['sort_order'] = 100;
        }
        if (!array_key_exists('sort_order', $b)) {
            $b['sort_order'] = 100;
        }
        $a = (int)$a['sort_order'];
        $b = (int)$b['sort_order'];

        if ($a < $b) {
            return -1;
        }

        if ($a === $b) {
            return 0;
        }

        if ($a > $b) {
            return 1;
        }
    }

}
