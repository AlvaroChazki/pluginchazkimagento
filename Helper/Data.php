<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const REFERENCE_ATTRIBUTE_CODE = 'reference_note';
    public const RUC_NUMBER_ATTRIBUTE_CODE = 'ruc_number';
    public const REFERENCE_ADDRESS_CODE = 'reference_address';

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Custom Log
     *
     * @param $msg
     * @param bool $echo
     */
    public function log($msg, $echo = false)
    {
        $this->_logger->info($msg);

        if ($echo) {
            echo PHP_EOL . $msg;
        }
    }

    /**
     * @param $getOrSet
     * @param $attribute
     * @return string
     */
    public function getFunctionName($getOrSet, $attribute): string
    {
        return $getOrSet . str_replace('_', '', ucwords($attribute, '_'));
    }
}
