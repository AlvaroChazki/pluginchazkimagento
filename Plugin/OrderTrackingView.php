<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Plugin;

use Magento\Framework\Phrase;
use Magento\Shipping\Block\Adminhtml\Order\Tracking\View;
use Chazki\ChazkiArg\Model\ChazkiArg;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class OrderTrackingView
{
    /**
     * @param View $subject
     * @param $result
     * @param $code
     * @return Phrase
     * @throws Zend_Log_Exception
     */
    public function afterGetCarrierTitle(View $subject, $result, $code): Phrase
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $result ' . $code === ChazkiArg::TRACKING_CODE ? __(ChazkiArg::TRACKING_LABEL) : __('Custom Value'));

        return $code === ChazkiArg::TRACKING_CODE ? __(ChazkiArg::TRACKING_LABEL) : __('Custom Value');
    }
}
