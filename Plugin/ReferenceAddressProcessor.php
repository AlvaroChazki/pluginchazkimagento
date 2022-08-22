<?php

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class ReferenceAddressProcessor
{
    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * ReferenceAddressProcessor constructor.
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

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children'][HelperData::REFERENCE_ADDRESS_CODE] = [
                'label' => __('Reference Address'),
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'customCheckoutForm',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'tooltip' => [
                        'description' => __('Customer Reference Address.'),
                    ],
                ],
                'dataScope' => 'shippingAddress.custom_attributes' . '.' . HelperData::REFERENCE_ADDRESS_CODE,
                'sortOrder' => 220,
                'provider' => 'checkoutProvider',
                'filterBy' => null,
                'customEntry' => null,
                'visible' => true,
                'value' => ''
            ];
        } elseif (
            isset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'][HelperData::REFERENCE_ADDRESS_CODE]
            )
        ) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'][HelperData::REFERENCE_ADDRESS_CODE]
            );
        }

        return $jsLayout;
    }
}
