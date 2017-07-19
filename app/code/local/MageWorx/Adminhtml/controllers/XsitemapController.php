<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_XsitemapController extends  Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/xsitemap_generate')
            ->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'))
            ->_addBreadcrumb(Mage::helper('xsitemap')->__('Google Sitemap (Extended)'), Mage::helper('xsitemap')->__('Google Sitemap (Extended)'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('mageworx/xsitemap'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('sitemap_id');
        $model = Mage::getModel('xsitemap/sitemap');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xsitemap')->__('This sitemap no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        Mage::register('sitemap_sitemap', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('xsitemap')->__('Edit Sitemap') : Mage::helper('xsitemap')->__('New Sitemap'), $id ? Mage::helper('xsitemap')->__('Edit Sitemap') : Mage::helper('xsitemap')->__('New Sitemap'))
            ->_addContent($this->getLayout()->createBlock('mageworx/xsitemap_edit'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('xsitemap/sitemap');

            if ($this->getRequest()->getParam('sitemap_id')) {
                $model ->load($this->getRequest()->getParam('sitemap_id'));

                if ($model->getSitemapFilename() && file_exists($model->getPreparedFilename())){
                    unlink($model->getPreparedFilename());
                }
            }


            $model->setData($data);

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('xsitemap')->__('Sitemap was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('sitemap_id' => $model->getId()));
                    return;
                }
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('sitemap_id', $model->getId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('sitemap_id' => $this->getRequest()->getParam('sitemap_id')));
                return;
            }
        }
        $this->_redirect('*/*/');

    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('sitemap_id')) {
            try {
                $model = Mage::getModel('xsitemap/sitemap');
                $model->setId($id);

                /* @var $sitemap MageWorx_XSitemap_Model_Sitemap */
                $model->load($id);
                if ($model->getSitemapFilename() && file_exists($model->getPreparedFilename())){
                    unlink($model->getPreparedFilename());
                }
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('xsitemap')->__('Sitemap was successfully deleted'));
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('sitemap_id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('xsitemap')->__('Unable to find a sitemap to delete'));
        $this->_redirect('*/*/');
    }

    public function generateAction()
    {
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = Mage::getModel('xsitemap/sitemap');
        /* @var $sitemap MageWorx_XSitemap_Model_Sitemap */
        $sitemap->load($id);
        if ($sitemap->getId()) {
            try {
                $sitemap->generateXml();

                $this->_getSession()->addSuccess(Mage::helper('xsitemap')->__('Sitemap "%s" has been successfully generated', $sitemap->getSitemapFilename()));
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('xsitemap')->__('Unable to generate a sitemap'));
                //$this->_getSession()->addError($e);
            }
        }
        else {
            $this->_getSession()->addError(Mage::helper('xsitemap')->__('Unable to find a sitemap to generate'));
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/xsitemap');
    }

}