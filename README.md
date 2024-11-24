# Sanjeev_GeolocationCurrency

Sanjeev_GeolocationCurrency is a module for Magento 2. This module sets currency for the user, according to geolocation mapping via ipinfo.io api.

## Install with Composer
```
composer require sanjeev-kr/geolocation-currency
php bin/magento module:enable Sanjeev_GeolocationCurrency
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

## Install Manually
- Download zip and extract
- Create a new directory Sanjeev/GeolocationCurrency in app/code directory and copy and paste files in Sanjeev/GeolocationCurrency directory.
- And run below commands

```
php bin/magento module:enable Sanjeev_GeolocationCurrency
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```
## How to use
- In admin go to Stores -> Configuration -> GENERAL -> Currency Setup -> Currency Options
  -> Allowed Currencies
- Select two or more currencies including Base Currency
- In admin go to Stores -> Configuration -> GENERAL -> Currency Setup -> Geolocation Currency
  -> Enable set Yes	 and enter ipinfo.io Token
- In admin go to Stores -> Currency Rates and add rates for the allowed currencies either manually or via API.
- In admin go to Stores -> Geolocation Currency -> Map Currency Country.
- Add New Record to map the currency and country. Please note map only those currencies which are allowed in second step.

