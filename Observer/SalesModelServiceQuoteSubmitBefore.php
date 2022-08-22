<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Observer;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Zend_Log_Exception;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * SalesModelServiceQuoteSubmitBefore constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws Zend_Log_Exception
     */
    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__);

        if ($this->helperData->getEnabled()) {
            /** @var Order $order */
            $order = $observer->getEvent()->getData('order');

            /** @var Quote $quote */
            $quote = $observer->getEvent()->getData('quote');

            $shippingOrder = $order->getBillingAddress()->getData();
            $shippingAddressData = $quote->getShippingAddress()->getData();
            $shippingAddressDataArray = $quote->getCustomAttributes();

            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingOrder ' . json_encode($shippingOrder));
            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddressData ' . json_encode($shippingAddressData));
            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddressDataArray ' . json_encode($shippingAddressDataArray));

            // Shipping Address

            if (isset($shippingAddressData[HelperData::REFERENCE_ATTRIBUTE_CODE])) {
                $order->getShippingAddress()->setData(
                    HelperData::REFERENCE_ATTRIBUTE_CODE,
                    $shippingAddressData[HelperData::REFERENCE_ATTRIBUTE_CODE]
                );

                $order->getBillingAddress()->setData(
                    HelperData::REFERENCE_ATTRIBUTE_CODE,
                    $shippingAddressData[HelperData::REFERENCE_ATTRIBUTE_CODE]
                );
            }

            if (isset($shippingAddressData[HelperData::RUC_NUMBER_ATTRIBUTE_CODE])) {
                $order->getShippingAddress()->setData(
                    HelperData::RUC_NUMBER_ATTRIBUTE_CODE,
                    $shippingAddressData[HelperData::RUC_NUMBER_ATTRIBUTE_CODE]
                );

                $order->getBillingAddress()->setData(
                    HelperData::RUC_NUMBER_ATTRIBUTE_CODE,
                    $shippingAddressData[HelperData::RUC_NUMBER_ATTRIBUTE_CODE]
                );
            }

            if (isset($shippingAddressData[HelperData::REFERENCE_ADDRESS_CODE])) {
                $order->getShippingAddress()->setData(
                    HelperData::REFERENCE_ADDRESS_CODE,
                    $shippingAddressData[HelperData::REFERENCE_ADDRESS_CODE]
                );

                $order->getBillingAddress()->setData(
                    HelperData::REFERENCE_ADDRESS_CODE,
                    $shippingAddressData[HelperData::REFERENCE_ADDRESS_CODE]
                );
            }


            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddressData ' . json_encode($shippingAddressData));
            $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddressData ' . json_encode($order));
        }

        return $this;
    }
}
