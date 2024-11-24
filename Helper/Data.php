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

namespace Sanjeev\GeolocationCurrency\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Sanjeev\GeolocationCurrency\Model\GeolocationCurrencyFactory;

class Data extends AbstractHelper
{
    protected $scopeConfig;
    protected $storeManager;
    protected $httpClient;
    protected $curl;
    protected $remoteAddress;
    protected $sessionManager;
    protected $debugLogger;
    protected $geolocationCurrencyFactory;
    protected $logFile ="country_currency.log";
    
    const XML_CONFIG_SYSTEM_GEOLOCATION_ENABLED = 'currency/general/enable';
    const XML_CONFIG_SYSTEM_GEOLOCATION_IPINFO_TOKEN = 'currency/general/ipinfo_token';
    const XML_CONFIG_SYSTEM_GEOLOCATION_DEBUG  = 'currency/general/enable_debug';
    const XML_CONFIG_GENERAL_CURRENCY_OPTION_ALLOW  = 'currency/options/allow';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        GeolocationCurrencyFactory  $geolocationCurrencyFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->remoteAddress = $remoteAddress;
        $this->sessionManager = $sessionManager;
        $this->geolocationCurrencyFactory = $geolocationCurrencyFactory;
    }

    public function getConfig($path)
    {
        $configValue = $this->scopeConfig->getValue($path ,ScopeInterface::SCOPE_STORE);
        return $configValue;
    }

    public function getAllowedCurrencies() {

        $allowedCurrencies =  $this->getConfig(self::XML_CONFIG_GENERAL_CURRENCY_OPTION_ALLOW);

        return explode(',' , ($allowedCurrencies ? (string)$allowedCurrencies : ''));
    }

    public function getClientCountry() {
        $this->getClientCountryFromIpinfo();
    }

    public function getClientCountryFromIpinfo()
    {
        $ip = $this->getRemoteAddress();
        $apiUrl = "https://ipinfo.io/".$ip;
        $accessToken = $this->getConfig(self::XML_CONFIG_SYSTEM_GEOLOCATION_IPINFO_TOKEN);
        $this->writeDebug("Ip $ip ");
        $this->writeDebug("Ipinfo accessToken ", $accessToken);

        $country = null;
        try{
            if(!empty($accessToken)) {
                $this->curl->addHeader("Authorization", "Bearer $accessToken");
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->setOption(CURLOPT_MAXREDIRS, 10);
                $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
                $this->curl->get($apiUrl);
                $response = $this->curl->getBody();
                $this->writeDebug("Ipinfo response ", $response);
                $responseArray = json_decode($response);
                return isset($responseArray->country)? $responseArray->country: null;

            }
        } catch (Exception $e) {
            $this->writeDebug("Ipinfo exception ", $e->getMessage());
        }
        return $country;
    }

    public function getRemoteAddress()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    public function getCountryCurrency()
    {
           $country = $this->getClientCountry();
           $currency = null;
           if($country != null) {
            $geolocationCurrency = $this->geolocationCurrencyFactory->create();
            $currencyData = $geolocationCurrency->getCollection()->addFieldToFilter('country', $country)
            ->getFirstItem();
             if ($currencyData->getId()) {
                $currency = $currencyData->getCurrency();
             }
           }
        return $currency;
    }

    public function isDebugLogEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_SYSTEM_GEOLOCATION_DEBUG);
    }

    public function writeDebug($message, $data = null)
    {
        if ($this->isDebugLogEnabled()) {
            $logger = $this->getDebugLogger();
            $logger->info($message);
            if ($data !== null) $logger->info(print_r($data, true));
        }
    }

    protected function getDebugLogger()
    {
        if (!$this->debugLogger) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/' . $this->logFile);
            $this->debugLogger = new \Zend_Log();
            $this->debugLogger->addWriter($writer);
        }

        return  $this->debugLogger;
    }
}
