<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */
namespace Chazki\ChazkiArg\Model\Carrier;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiScheduledFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

/**
 * ChazkiArg shipping model
 */
class ChazkiScheduled extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'chazkiargscheduled';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var string
     */
    protected $_defaultConditionName = 'package_weight';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_resultMethodFactory;

    /**
     * @var ChazkiScheduledFactory
     */
    protected $_chazkiRatesFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory
     * @param ChazkiScheduledFactory $chazkiRatesFactory
     * @param array $data
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory,
        ChazkiScheduledFactory $chazkiRatesFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_resultMethodFactory = $resultMethodFactory;
        $this->_chazkiRatesFactory = $chazkiRatesFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect rates.
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // Free shipping by qty
        $freeQty = 0;
        $freePackageValue = 0;

//        if ($request->getAllItems()) {
//            foreach ($request->getAllItems() as $item) {
//                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
//                    continue;
//                }
//
//                if ($item->getHasChildren() && $item->isShipSeparately()) {
//                    foreach ($item->getChildren() as $child) {
//                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
//                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
//                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
//                        }
//                    }
//                } elseif ($item->getFreeShipping() || $item->getAddress()->getFreeShipping()) {
//                    $freeShipping = $item->getFreeShipping() ?
//                        $item->getFreeShipping() : $item->getAddress()->getFreeShipping();
//                    $freeShipping = is_numeric($freeShipping) ? $freeShipping : 0;
//                    $freeQty += $item->getQty() - $freeShipping;
//                    $freePackageValue += $item->getBaseRowTotal();
//                }
//            }
//            $oldValue = $request->getPackageValue();
//            $request->setPackageValue($oldValue - $freePackageValue);
//        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        $rate = $this->getRate($request);

        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        $logger->info(__METHOD__ . "-" . __LINE__);
        $logger->info(print_r($rate, true));

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        if (!empty($rate) && $rate['price'] >= 0) {
            if ($request->getPackageQty() == $freeQty) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }
            $method = $this->createShippingMethod($shippingPrice, $rate['cost']);
            $result->append($method);
        } elseif ($request->getPackageQty() == $freeQty) {

            /**
             * Promotion rule was applied for the whole cart.
             *  In this case all other shipping methods could be omitted
             * Table rate shipping method with 0$ price must be shown if grand total is more than minimal value.
             * Free package weight has been already taken into account.
             */
            $request->setPackageValue($freePackageValue);
            $request->setPackageQty($freeQty);
            $rate = $this->getRate($request);
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = $this->createShippingMethod(0, 0);
                $result->append($method);
            }
        } else {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('specificerrmsg'),
                    ],
                ]
            );
            $result->append($error);
        }

        return $result;
    }

    /**
     * Get rate.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        return $this->_chazkiRatesFactory->create()->getRate($request);
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Get the method object based on the shipping price and cost
     *
     * @param float $shippingPrice
     * @param float $cost
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createShippingMethod($shippingPrice, $cost)
    {
        /** @var  \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_resultMethodFactory->create();

        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title') . ' (Leyenda Envio programado)');

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($cost);
        return $method;
    }
}
