<?php
/**
 * Copyright 2024 Sanjeev Kumar
 * NOTICE OF LICENSE
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Sanjeev
 * @package   Sanjeev_GeolocationCurrency
 * @copyright Copyright (c) 2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
namespace Sanjeev\GeolocationCurrency\Controller\Adminhtml\Geolocation;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var Session
     */
    protected $adminsession;
    protected $geolocationFactory;
    protected $geolocationResourceModel;
    protected $resultRedirectFactory;

    /**
     * @param Action\Context $context
     * @param Session        $adminsession
     */
    public function __construct(
        Action\Context $context,
        \Sanjeev\GeolocationCurrency\Model\GeolocationCurrencyFactory $geolocationFactory,
        \Sanjeev\GeolocationCurrency\Model\ResourceModel\GeolocationCurrency $geolocationResourceModel,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        Session $adminsession
    ) {
        parent::__construct($context);
        $this->geolocationFactory = $geolocationFactory;
        $this->geolocationResourceModel = $geolocationResourceModel;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->adminsession = $adminsession;
    }
    /**
     * Save redirect url record action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirectFactory = $this->resultRedirectFactory->create();
        $postData = $this->getRequest()->getPostValue();
        $data = [
            'currency' => $postData["currency"],
            'country' => $postData['country']
        ];

        $url_id = $this->getRequest()->getParam('id');
        if ($data) {
            $userData = $this->geolocationFactory->create();
            if ($url_id) {
                $userData->load($url_id);
                $data = [
                    'id' => $url_id,
                    'currency' => $postData["currency"],
                    'country' => $postData["country"]
                ];
            }
            $userData->setData($data);
            try {
                $this->geolocationResourceModel->save($userData);
                $this->messageManager->addSuccess(__('The data has been saved.'));
                $this->adminsession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'add') {
                        return   $resultRedirectFactory->setPath('*/*/add');
                    } else {
                        return   $resultRedirectFactory->setPath('*/*/edit', ['id' => $userData->getUrlId(), '_current' => true]);
                    }
                }
                return   $resultRedirectFactory->setPath('*/*/');
            } catch(\Magento\Framework\Exception\AlreadyExistsException $e){  
                $this->messageManager->addErrorMessage(__('The country already exists. Please choose a different country.'));  
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                die($e);
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }
            $this->_getSession()->setFormData($data);
            return   $resultRedirectFactory->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return   $resultRedirectFactory->setPath('*/*/');
    }
}
