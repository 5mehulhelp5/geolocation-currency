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
namespace Sanjeev\GeolocationCurrency\Plugin;

use Sanjeev\GeolocationCurrency\Helper\Data as CurrencyHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Action 
{
    protected $scopeConfig = null;

    protected $request = null;

    protected $httpResponse = null;

    protected $storeManager = null;

    protected $sessionManager = null;

    protected $currencyHelper = null;

    protected $logger = null;


    public function __construct(
       RequestInterface $request,
       Http $httpResponse,
       ScopeConfigInterface $scopeConfig,
       StoreManagerInterface $storeManager,
       SessionManagerInterface $sessionManager,
       CurrencyHelper $currencyHelper,
       LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->httpResponse = $httpResponse;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->sessionManager = $sessionManager;
        $this->currencyHelper = $currencyHelper;
        $this->logger = $logger;
    }

    public function aroundDispatch(
        FrontControllerInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $scopeStores = ScopeInterface::SCOPE_STORES;

        $this->currencyHelper->writeDebug("SERVER DATA", $_SERVER);

        if(method_exists($this->httpResponse, "getHeaders")) {
            $this->currencyHelper->writeDebug("HEADERS", $this->httpResponse->getHeaders());
        }

        if (!$this->scopeConfig->isSetFlag(CurrencyHelper::XML_CONFIG_SYSTEM_GEOLOCATION_ENABLED,
             $scopeStores, $currentStoreId)
            || $this->skipCurrencySetup()
        ) {
            $this->currencyHelper->writeDebug("Not Triggered");
            return $proceed($request);
        }

        $allowedCurrencies = $this->currencyHelper->getAllowedCurrencies();
        $currency = $this->currencyHelper->getCountryCurrency();

        if($currency !== null && in_array($currency, $allowedCurrencies)) {
            
            $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
            $this->httpResponse->setNoCacheHeaders();
            $this->currencyHelper->writeDebug("Triggered FOR $currency");  
        }

        $this->sessionManager->setCurrencyCodeForCustomer("1");
        return $proceed($request);
    }

    protected function skipCurrencySetup() {

        return ($this->sessionManager->getCurrencyCodeForCustomer() === "1");
    }

}
