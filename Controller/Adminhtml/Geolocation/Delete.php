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

class Delete extends \Magento\Backend\App\Action
{
   
    public $geolocationFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Sanjeev\GeolocationCurrency\Model\GeolocationCurrencyFactory $geolocationFactory
    ) {
        $this->geolocationFactory = $geolocationFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id && (int) $id > 0) {
            try {
                $model =  $this->geolocationFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The Data has been deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/index');
            }
        }
        $this->messageManager->addError(__('Data doesn\'t exist any longer.'));
        return $resultRedirect->setPath('*/*/index');
    }

}
