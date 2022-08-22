<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates;

use Chazki\ChazkiArg\Model\Carrier\ChazkiScheduled as ModelChazkiScheduled;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiScheduled as ResourceModelChazkiScheduled;

/**
 * Shipping table rates collection
 *
 * @api
 * @since 100.0.2
 */
class ChazkiScheduledCollection extends Collection
{
    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelChazkiScheduled::class,
            ResourceModelChazkiScheduled::class
        );

        $this->_countryTable = $this->getTable('directory_country');
        $this->_regionTable = $this->getTable('directory_country_region');
    }
}
