<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates;

use Chazki\ChazkiArg\Model\Carrier\ChazkiExpress as ModelChazkiExpress;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiExpress as ResourceModelChazkiExpress;

/**
 * Shipping table rates collection
 *
 * @api
 * @since 100.0.2
 */
class ChazkiExpressCollection extends Collection
{
    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelChazkiExpress::class,
            ResourceModelChazkiExpress::class
        );

        $this->_countryTable = $this->getTable('directory_country');
        $this->_regionTable = $this->getTable('directory_country_region');
    }
}
