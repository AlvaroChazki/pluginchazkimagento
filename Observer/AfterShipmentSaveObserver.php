<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Observer;

use Chazki\ChazkiArg\Model\ChazkiArg;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class AfterShipmentSaveObserver implements ObserverInterface
{
    /**
     * @var ChazkiArg
     */
    protected ChazkiArg $chazkiArg;

    /**
     * AfterShipmentSaveObserver constructor.
     * @param ChazkiArg $chazkiArg
     */
    public function __construct(
        ChazkiArg $chazkiArg
    ) {
        $this->chazkiArg = $chazkiArg;
    }

    /**
     * @param Observer $observer
     * @throws Zend_Log_Exception
     */
    public function execute(Observer $observer)
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        /** @var Shipment $shipping */
        $shipping = $observer->getEvent()->getShipment();
        $order = $shipping->getOrder();
        $shippingMethod = $order->getShippingMethod(true);
//        $carrierCode = $shippingMethod->getData('carrier_code');


        $track = $shipping->getTracks();
        $carrierCode= $track[0]->getCarrierCode();

// chazkiargregular

//        if (strpos($carrierCode, 'chazki') !== false) {
        if (strpos($carrierCode, 'chazki_arg') !== false) {
            $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $shipping ' . json_encode($shipping));

            $this->chazkiArg->createShipment($shipping);
        }
    }
}
