<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class CheckoutProcessor
{
    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * CheckoutProcessor constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Checkout LayoutProcessor after process plugin.
     *
     * @param LayoutProcessor $processor
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Zend_Log_Exception
     */
    public function afterProcess(LayoutProcessor $processor, array $jsLayout): array
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__);

        if ($this->helperData->getEnabled()) {
            $referenceNote = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress.custom_attributes',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'tooltip' => [
                        'description' => __('Set the reference for the delivery of the order.'),
                    ],
                ],
                'dataScope' => 'shippingAddress.custom_attributes' . '.' . HelperData::REFERENCE_ATTRIBUTE_CODE,
                'label' => __('Reference Note'),
                'provider' => 'checkoutProvider',
                'sortOrder' => 200,
                'options' => [],
                'filterBy' => null,
                'customEntry' => null,
                'visible' => true,
                'value' => ''
            ];

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']
            [HelperData::REFERENCE_ATTRIBUTE_CODE] = $referenceNote;
        } elseif (
            isset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']
                [HelperData::REFERENCE_ATTRIBUTE_CODE]
            )
        ) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']
                [HelperData::REFERENCE_ATTRIBUTE_CODE]
            );
        }

        return $jsLayout;
    }
}
