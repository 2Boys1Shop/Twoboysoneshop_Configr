<?php

class Twoboysoneshop_Configr_Configr_HistoryController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system')
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('Configuration'), $this->__('Configuration'))
            ->_addBreadcrumb($this->__('History'), $this->__('History'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Configuration'))
             ->_title($this->__('History'));

        if (!Mage::helper('configr')->isHistoryEnabled()) {
            Mage::getSingleton('adminhtml/session')->addNotice($this->__('Tracking of config changes is currently disabled.'));
        }
        
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('configr/history'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('History'));

        $id = (int)$this->getRequest()->getParam('history_id');
        $history = Mage::getModel('configr/history');

        if ($id) {
            $history->load($id);
            if (! $history->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This config history entry no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($history->getId() ? $history->getName() : $this->__('ID #') . $id);

        Mage::register('history_entry', $history);

        $this->_initAction()
            ->_addBreadcrumb($this->__('View History'), $this->__('View History'))
            ->_addContent($this->getLayout()->createBlock('configr/history_edit')->setData('action', $this->getUrl('*/history/save')))
            ->_addLeft($this->getLayout()->createBlock('configr/history_edit_tabs'));

        $this->renderLayout();
    }

    public function restoreAction()
    {
        if (!Mage::helper('configr')->isHistoryEnabled()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Tracking of config changes is currently disabled.'));
            $this->_redirect('*/*/');
        }
        
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('history_id');
            $oldHistory = Mage::getModel('configr/history')->load($id);
            if (!$oldHistory->getId() || !$id) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This config history entry no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $setup = new Mage_Core_Model_Resource_Setup('core_write');
                $setup->setConfigData(
                    $oldHistory->getPath(),
                    $oldHistory->getOldValue(),
                    $oldHistory->getScope(),
                    (int)$oldHistory->getScopeId()
                );
            
                $newHistory = Mage::getModel('configr/history');
                $newHistory->setData(array(
                    'scope'      => $oldHistory->getScope(),
                    'scope_id'   => $oldHistory->getScopeId(),
                    'path'       => $oldHistory->getPath(),
                    'old_value'  => $oldHistory->getValue(),
                    'value'      => $oldHistory->getOldValue(),
                    'user_id'    => Mage::getSingleton('admin/session')->getUser()->getId(),
                    'user_name'  => Mage::getSingleton('admin/session')->getUser()->getUsername(),
                    'created_at' => Mage::getModel('core/date')->gmtDate(),
                    'restored_from_id' => $oldHistory->getId(),
                ))->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The config value has been restored.'));
                $this->_redirect('*/*/edit', array('history_id' => $oldHistory->getId()));
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('history_id' => $oldHistory->getId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function createMigrationAction() 
    {
        $historyIds = $this->getRequest()->getPost('history_ids', array());
        sort($historyIds);
        
        
        $sqlScript   = array();
        
        $phpScript   = array();
        $phpScript[] = '<?php';
        $phpScript[] = '$installer = $this;';
        $phpScript[] = '$installer->startSetup();';
        $phpScript[] = '';
        
        $sqlPlaceholder = 'REPLACE INTO core_config_data (scope, scope_id, path, value) values (\'%s\', %d, \'%s\', \'%s\');';
        $phpPlaceholder = '$installer->setConfigData(\'%s\', \'%s\', \'%s\', %s);';
        
        foreach ($historyIds as $historyId) {
            $history = Mage::getModel('configr/history')->load($historyId);
            
            // SQL
            $sqlScript[] = '-- Change #' . $history->getId() . ' (' . Mage::helper('core')->formatDate($history->getCreatedAt(), 'medium', true) . ')';
            $sqlScript[] = vsprintf($sqlPlaceholder, array(
                $history->getScope(),
                (int)$history->getScopeId(),
                $history->getPath(),
                str_replace("'", "''", $history->getValue())
            ));
            $sqlScript[] = '';
            
            // PHP
            $phpScript[] = '// Change #' . $history->getId() . ' (' . Mage::helper('core')->formatDate($history->getCreatedAt(), 'medium', true) . ')';
            $phpScript[] = vsprintf($phpPlaceholder, array(
                $history->getPath(),
                str_replace("'", "\'\'", $history->getValue()),
                $history->getScope(),
                (int)$history->getScopeId()
            ));
            $phpScript[] = '';
        }
        
        $phpScript[] = '';
        $phpScript[] = '$installer->endSetup();';
        
        $this->loadLayout()
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/template')
                    ->setTemplate('twoboysoneshop_configr/history/migration.phtml')
                    ->assign('_phpScript', $phpScript)
                    ->assign('_sqlScript', $sqlScript)
            )
            ->renderLayout();
    }
    
    public function historyGridAction()
    {
        $this->getResponse()
            ->setBody($this->getLayout()
            ->createBlock('configr/history_grid')
            ->toHtml()
        );
    }

}
