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
namespace Sanjeev\GeolocationCurrency\Ui\Component\Listing\Column;

use Magento\Framework\Option\ArrayInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Country extends Column
{
    protected $countryOptions;

    public function __construct(
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->countryOptions = $localeLists->getOptionCountries();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['country'])) {
                    $item['country'] = $this->getCountryName($item['country']);
                }
            }
        }
        return $dataSource;
    }

    public function getCountryName($countryCode)
    {
        foreach ($this->countryOptions as $option) {
            if ($option['value'] === $countryCode) {
                return $option['label'];
            }
        }
        return $countryCode;
    }
}
