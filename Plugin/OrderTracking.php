<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Model\ChazkiArg;
use Magento\Shipping\Block\Adminhtml\Order\Tracking;

class OrderTracking
{
    /**
     * @param Tracking $subject +
     * @param $result
     * @return mixed
     */
    public function afterGetCarriers(Tracking $subject, $result)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

//        $dataTitle = __(ChazkiArg::getTitleTitleCarrierShippingInfo());
//        $ejm = new ChazkiArg();
//        $dataTitle = $ejm->getTitleTitleCarrierShippingInfo();
//        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $dataTitle ' . $dataTitle);

        $result[ChazkiArg::TRACKING_CODE] = __(ChazkiArg::TRACKING_LABEL);

        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $result ' . json_encode($result));

        return $result;
    }
}
