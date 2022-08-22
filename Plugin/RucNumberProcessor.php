<?php

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class RucNumberProcessor
{
    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * RucNumberProcessor constructor.
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

        if ($this->helperData->getEnabled()) {
            $logger->info(__METHOD__ . "-" . __LINE__);

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children'][HelperData::RUC_NUMBER_ATTRIBUTE_CODE] = [
                'label' => __('Ruc Number'),
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'customCheckoutForm',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'tooltip' => [
                        'description' => __('Ruc Number.'),
                    ],
                ],
                'dataScope' => 'shippingAddress.custom_attributes' . '.' . HelperData::RUC_NUMBER_ATTRIBUTE_CODE,
                'sortOrder' => 210,
                'provider' => 'checkoutProvider',
                'filterBy' => null,
                'customEntry' => null,
                'visible' => true,
                'value' => ''
            ];

        } elseif (
            isset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'][HelperData::RUC_NUMBER_ATTRIBUTE_CODE]
            )
        ) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'][HelperData::RUC_NUMBER_ATTRIBUTE_CODE]
            );
        }

        return $jsLayout;
    }
}
