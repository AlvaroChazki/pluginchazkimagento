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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class OrderShipmentSave
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ChazkiArg
     */
    protected ChazkiArg $chazkiArg;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $resultRedirectFactory;

    /**
     * OrderShipmentSave constructor.
     * @param RequestInterface $request
     * @param ChazkiArg $chazkiArg
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        RequestInterface $request,
        ChazkiArg $chazkiArg,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->request = $request;
        $this->chazkiArg = $chazkiArg;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param Save $subject
     * @param callable $proceed
     * @return mixed
     * @throws Zend_Log_Exception
     */
    public function aroundExecute(Save $subject, callable $proceed)
    {
        $tracking = $this->request->getParam('tracking');
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__);

        if (isset($tracking) && count($tracking)) {
            $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $tracking ' . json_encode($tracking));
            foreach ($tracking as $track) {
                if ($track['carrier_code'] === ChazkiArg::TRACKING_CODE) {
                    $trackPrefix = $this->scopeConfig->getValue('shipping/chazki_arg/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $shipment = $this->chazkiArg->getShipment($trackPrefix . $track['number']);
                    $shipment = json_decode($shipment, true);
                    $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $shipment ' . json_encode($shipment));

                    if (
                        isset($shipment) &&
                        isset($shipment['shipment']) &&
                        isset($shipment['shipment']['tracking']) &&
                        $shipment['shipment']['tracking'] === $trackPrefix . $track['number']
                    ) {
                        /** @var Redirect $resultRedirect */
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $this->messageManager->addErrorMessage(__("The tracking ID " . $trackPrefix . $track['number'] . " is already in use"));

                        return $resultRedirect->setPath('*/*/new', ['order_id' => $this->request->getParam('order_id')]);
                    }
                }
            }
        }

        return $proceed();
    }
}
