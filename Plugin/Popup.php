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
use Magento\Framework\DataObject;
use Magento\Shipping\Block\Tracking\Popup as OriginalPopup;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class Popup
{
    /**
     * @var ChazkiArg
     */
    protected ChazkiArg $chazkiArg;

    /**
     * Popup constructor.
     * @param ChazkiArg $chazkiArg
     */
    public function __construct(
        ChazkiArg $chazkiArg
    ) {
        $this->chazkiArg = $chazkiArg;
    }

    /**
     * @param OriginalPopup $subject
     * @param $result
     * @return mixed
     * @throws Zend_Log_Exception
     */
    public function afterGetTrackingInfo(OriginalPopup $subject, $result)
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        $chazkiLabel = __(ChazkiArg::TRACKING_LABEL)->getText();

        foreach ($result as $key => $values) {

            foreach ($values as $keys => $info) {
                $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $info ' . json_encode($info));
                $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $info ' . $info);

                if (is_array($info) && $info['title'] === $chazkiLabel) {
                    $tracking[$keys] = new DataObject();

                    if (isset($info['number'])) {
                        $tracking[$keys]->setTracking($info['number']);

                        try {
                            $trackingData = $this->chazkiArg->getShipment($info['number']);
                            $trackingData = json_decode($trackingData, true);

                            if (isset($trackingData) && isset($trackingData['shipment']) && isset($trackingData['shipment']['history'])) {
                                $history = [];

                                foreach ($trackingData['shipment']['history'] as $data) {
                                    $history[] = [
                                        'deliverydate' => date('Y-m-d', strtotime($data['date'])),
                                        'deliverytime' => date('H:i:s', strtotime($data['date'])),
                                        'deliverylocation' => $data['location'],
                                        'activity' => $data['description']
                                    ];
                                }

                                if (isset($trackingData['shipment']['completed'])) {
                                    $deliveryLocation = '';
                                    if (isset($trackingData['shipment']['contact']['address']['locality'])) {
                                        $deliveryLocation = $trackingData['shipment']['contact']['address']['locality'];
                                    } elseif (isset($trackingData['shipment']['contact']['address']['sublocality'])) {
                                        $deliveryLocation = $trackingData['shipment']['contact']['address']['sublocality'];
                                    } elseif (isset($trackingData['shipment']['contact']['address']['region2'])) {
                                        $deliveryLocation = $trackingData['shipment']['contact']['address']['region2'];
                                    }

                                    $history[] = [
                                        'deliverydate' => date('Y-m-d', strtotime($trackingData['shipment']['completed'])),
                                        'deliverytime' => date('H:i:s', strtotime($trackingData['shipment']['completed'])),
                                        'deliverylocation' => $deliveryLocation,
                                        'activity' => __('Completed')
                                    ];
                                } elseif (isset($trackingData['shipment']['returned'])) {
                                    $history[] = [
                                        'deliverydate' => date('Y-m-d', strtotime($trackingData['shipment']['returned'])),
                                        'deliverytime' => date('H:i:s', strtotime($trackingData['shipment']['returned'])),
                                        'deliverylocation' => '',
                                        'activity' => __('Returned')
                                    ];
                                } elseif (isset($trackingData['shipment']['cancelled'])) {
                                    $history[] = [
                                        'deliverydate' => date('Y-m-d', strtotime($trackingData['shipment']['cancelled'])),
                                        'deliverytime' => date('H:i:s', strtotime($trackingData['shipment']['cancelled'])),
                                        'deliverylocation' => '',
                                        'activity' => __('Cancelled')
                                    ];
                                }

                                $tracking[$keys]->setProgressdetail($history);
                            }
                        } catch (\Exception $e) {
                            /** Continue */
                        }
                    }

                    if (isset($info['title'])) {
                        $tracking[$keys]->setCarrierTitle($info['title']);
                    }

                    $result[$key] = $tracking;
                }
            }
        }
        return $result;
    }
}
