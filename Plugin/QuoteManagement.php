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
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\QuoteManagement as OriginalQuoteManagement;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class QuoteManagement
{
    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * QuoteManagement constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param OriginalQuoteManagement $subject
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     * @throws Zend_Log_Exception
     */
    public function beforeSubmit(
        OriginalQuoteManagement $subject,
        QuoteEntity             $quote,
        array                   $orderData = []
    ) {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__);

        if ($this->helperData->getEnabled()) {
            $shippingAddress = $quote->getShippingAddress();
            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddress ' . json_encode($shippingAddress));

            if (isset($shippingAddress)) {
                $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddress ' . json_encode($shippingAddress));
                $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
                $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddressExtensionAttributes ' . json_encode($shippingAddressExtensionAttributes));

                if (isset($shippingAddressExtensionAttributes)) {
                    $referenceNote = $shippingAddress->getData(HelperData::REFERENCE_ATTRIBUTE_CODE);
                    $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('set', HelperData::REFERENCE_ATTRIBUTE_CODE)}($referenceNote);

                    $rucNumber = $shippingAddress->getData(HelperData::RUC_NUMBER_ATTRIBUTE_CODE);
                    $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('set', HelperData::RUC_NUMBER_ATTRIBUTE_CODE)}($rucNumber);

                    $referenceAddress = $shippingAddress->getData(HelperData::REFERENCE_ADDRESS_CODE);
                    $shippingAddressExtensionAttributes->{$this->helperData->getFunctionName('set', HelperData::REFERENCE_ADDRESS_CODE)}($referenceAddress);

                    $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddress ' . json_encode($shippingAddressExtensionAttributes));

                    $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);
                }
            }
        }
    }
}
