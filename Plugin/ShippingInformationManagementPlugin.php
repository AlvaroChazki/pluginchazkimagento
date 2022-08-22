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
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\QuoteRepository;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class ShippingInformationManagementPlugin
{
    /**
     * @var QuoteRepository
     */
    protected QuoteRepository $quoteRepository;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param QuoteRepository $quoteRepository
     * @param HelperData $helperData
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        HelperData      $helperData
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helperData = $helperData;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws Zend_Log_Exception
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface  $addressInformation
    ) {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        $logger->info(__METHOD__ . "-" . __LINE__);

        if ($this->helperData->getEnabled()) {
            $shippingAddress = $addressInformation->getShippingAddress();
            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddress ' . json_encode($shippingAddress));
            $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();

            if (count($shippingAddressExtensionAttributes->__toArray())) {
                $referenceNote = $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('get', HelperData::REFERENCE_ATTRIBUTE_CODE)}();
                $rucNumber = $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('get', HelperData::RUC_NUMBER_ATTRIBUTE_CODE)}();
                $referenceAddress = $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('get', HelperData::REFERENCE_ADDRESS_CODE)}();

                $shippingAddress->setData(HelperData::REFERENCE_ATTRIBUTE_CODE, $referenceNote);
                $shippingAddress->setData(HelperData::RUC_NUMBER_ATTRIBUTE_CODE, $rucNumber);
                $shippingAddress->setData(HelperData::REFERENCE_ADDRESS_CODE, $referenceAddress);

                $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddress ' . json_encode($shippingAddress));
            }
        }
    }
}
