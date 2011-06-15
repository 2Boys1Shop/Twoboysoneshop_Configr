<?php

class Twoboysoneshop_Configr_HistoryController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/acl')
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
             ->_title($this->__('Users'));

        $id = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('admin/user');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New User'));

        // Restore previously entered form data from session
        $data = Mage::getSingleton('adminhtml/session')->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('permissions_user', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? $this->__('Edit User') : $this->__('New User'), $id ? $this->__('Edit User') : $this->__('New User'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/permissions_user_edit')->setData('action', $this->getUrl('*/permissions_user/save')))
            ->_addLeft($this->getLayout()->createBlock('adminhtml/permissions_user_edit_tabs'));

        $this->_addJs($this->getLayout()->createBlock('adminhtml/template')->setTemplate('permissions/user_roles_grid_js.phtml'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('user_id');
            $model = Mage::getModel('admin/user')->load($id);
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->setData($data);

            /*
             * Unsetting new password and password confirmation if they are blank
             */
            if ($model->hasNewPassword() && $model->getNewPassword() === '') {
                $model->unsNewPassword();
            }
            if ($model->hasPasswordConfirmation() && $model->getPasswordConfirmation() === '') {
                $model->unsPasswordConfirmation();
            }

            $result = $model->validate();
            if (is_array($result)) {
                Mage::getSingleton('adminhtml/session')->setUserData($data);
                foreach ($result as $message) {
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
                $this->_redirect('*/*/edit', array('_current' => true));
                return $this;
            }

            try {
                $model->save();
                if ( $uRoles = $this->getRequest()->getParam('roles', false) ) {
                    /*parse_str($uRoles, $uRoles);
                    $uRoles = array_keys($uRoles);*/
                    if ( 1 == sizeof($uRoles) ) {
                        $model->setRoleIds($uRoles)
                            ->setRoleUserId($model->getUserId())
                            ->saveRelations();
                    } else if ( sizeof($uRoles) > 1 ) {
                        //@FIXME: stupid fix of previous multi-roles logic.
                        //@TODO:  make proper DB upgrade in the future revisions.
                        $rs = array();
                        $rs[0] = $uRoles[0];
                        $model->setRoleIds( $rs )->setRoleUserId( $model->getUserId() )->saveRelations();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The user has been saved.'));
                Mage::getSingleton('adminhtml/session')->setUserData(false);
                $this->_redirect('*/*/edit', array('user_id' => $model->getUserId()));
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setUserData($data);
                $this->_redirect('*/*/edit', array('user_id' => $model->getUserId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $currentUser = Mage::getSingleton('admin/session')->getUser();

        if ($id = $this->getRequest()->getParam('user_id')) {
            if ( $currentUser->getId() == $id ) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('You cannot delete your own account.'));
                $this->_redirect('*/*/edit', array('user_id' => $id));
                return;
            }
            try {
                $model = Mage::getModel('admin/user');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The user has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find a user to delete.'));
        $this->_redirect('*/*/');
    }

    public function rolesGridAction()
    {
        $id = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('admin/user');

        if ($id) {
            $model->load($id);
        }

        Mage::register('permissions_user', $model);
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/permissions_user_edit_tab_roles')->toHtml());
    }

    public function historyGridAction()
    {
        $this->getResponse()
            ->setBody($this->getLayout()
            ->createBlock('configr/history_grid')
            ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/acl/users');
    }

}
