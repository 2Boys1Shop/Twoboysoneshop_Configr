<?php
class Twoboysoneshop_Configr_OverviewController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections     = $configFields->getSections()->asArray();
        $tabs         = $configFields->getTabs()->asArray();
        $stores       = Mage::app()->getStores(true);
        $websites     = Mage::app()->getWebsites(true);
        $websitesCollection = Mage::app()->getWebsites(true);

        uasort($sections, array($this, '_sortByOrder'));
        uasort($tabs, array($this, '_sortByOrder'));
        ksort($stores);

        // Add sections to corresponding tabs
        foreach ($sections as $sectionKey => $section) {
            $tabs[$section['tab']]['sections'][$sectionKey] = $section;
        }

        // Convert websites to arrays
        foreach ($websites as $websiteKey => $website) {
            $websites[$websiteKey] = $website->getData();
        }

        // Convert stores to arrays
        foreach ($stores as $storeKey => $store) {
            $stores[$storeKey] = $store->getData();
            $websites[$store['website_id']]['stores'][$storeKey] = $store->getData();
        }
        $stores[0]['name'] = 'default';

        foreach ($tabs as &$tab) {
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

                        foreach ($stores as $storeId => $store) {
                            $field['stores'][$storeId] = Mage::getStoreConfig($configKey, $storeId);
                        }
                        
                        foreach ($websitesCollection as $websiteId => $website) {
                            $field['websites'][$websiteId] = $website->getConfig($configKey);
                        }
                    }
                }
            }
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('configr.overview.index')
                          ->addData(array(
                              'tabs'              => $tabs,
                              'stores'            => $stores,
                              'websites'          => $websites,
                          ));
        $this->renderLayout();
    }

    public function editAction()
    {
        $configKey = $this->getRequest()->getParam('configKey');
        $storeId   = $this->getRequest()->getParam('storeId');
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