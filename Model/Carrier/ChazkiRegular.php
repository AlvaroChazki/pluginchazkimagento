<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\Carrier;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiRegularFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use phpDocumentor\Reflection\Types\Boolean;
use Psr\Log\LoggerInterface;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

/**
 * ChazkiArg shipping model
 */
class ChazkiRegular extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'chazkiargregular';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var string
     */
    protected string $_defaultConditionName = 'package_weight';

    /**
     * @var ResultFactory
     */
    protected ResultFactory $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected MethodFactory $_resultMethodFactory;

    /**
     * @var ChazkiRegularFactory
     */
    protected ChazkiRegularFactory $_chazkiRatesFactory;

    /**
     * @var Context
     */
    protected Context $httpContext;

    /**
     * @var bool
     */
    protected Boolean $isLogged;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $resultMethodFactory
     * @param ChazkiRegularFactory $chazkiRatesFactory
     * @param Context $httpContext
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory         $rateErrorFactory,
        LoggerInterface      $logger,
        ResultFactory        $rateResultFactory,
        MethodFactory        $resultMethodFactory,
        ChazkiRegularFactory $chazkiRatesFactory,
        Context              $httpContext,
        array                $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_resultMethodFactory = $resultMethodFactory;
        $this->_chazkiRatesFactory = $chazkiRatesFactory;
        $this->httpContext = $httpContext;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect rates.
     *
     * @param RateRequest $request
     * @return Result
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
//
//            foreach ($request->getAllItems() as $item) {
//
//                $log1 = $item->getProduct()->isVirtual();
//                $log2 = $item->getParentItem();
//
//                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
//                    continue;
//                }
//
//                $log3 = $item->getHasChildren();
//                $log4 = $item->isShipSeparately();
//
//                if ($item->getHasChildren() && $item->isShipSeparately()) {
//
//                    $log5 = $item->getChildren();
//                    foreach ($item->getChildren() as $child) {
//
//                        $log6 = $child->getFreeShipping();
//                        $log7 = !$child->getProduct()->isVirtual();
//                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
////                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
//                            $freeShipping = 0;
//                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
//                        }
//                    }
//                } elseif ($item->getFreeShipping() || $item->getAddress()->getFreeShipping()) {
//
//                    $log8 = $item->getFreeShipping();
//                    $log9 = $item->getAddress()->getFreeShipping();
//
//
//                    $log10 = $item->getFreeShipping();
//                    $log11 = $item->getAddress()->getFreeShipping();
//
////                    $freeShipping = $item->getFreeShipping() ? $item->getFreeShipping() : $item->getAddress()->getFreeShipping();
////                    $freeShipping = is_numeric($freeShipping) ? $freeShipping : 0;
//                    $freeShipping = 0;
//
//                    $log11 = $item->getQty();
//                    $log11 = $item->getBaseRowTotal();
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

        /** @var Result $result */
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
//            $shippingCost = (float)$this->getConfigData('shipping_cost');
//            $method = $this->createShippingMethod($shippingCost, $shippingCost);
            $result->append($method);

//        } elseif ($request->getPackageQty() == $freeQty) {
//
//            /**
//             * Promotion rule was applied for the whole cart.
//             *  In this case all other shipping methods could be omitted
//             * Table rate shipping method with 0$ price must be shown if grand total is more than minimal value.
//             * Free package weight has been already taken into account.
//             */
//            $request->setPackageValue($freePackageValue);
//            $request->setPackageQty($freeQty);
//            $rate = $this->getRate($request);
//            if (!empty($rate) && $rate['price'] >= 0) {
////                $method = $this->createShippingMethod($shippingPrice, $rate['cost']);
////                $method = $this->createShippingMethod(0, 0);
//                $method = $this->createShippingMethod(500, 600);
//                $result->append($method);
//            }
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
     * @return Method
     * @throws Zend_Log_Exception
     */
    private function createShippingMethod($shippingPrice, $cost): Method
    {

//        if (1) {
//            return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
//        }

        /** @var  Method $method */
        $method = $this->_resultMethodFactory->create();

        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));


        $method->setPrice($shippingPrice);
        $method->setCost($cost);
        return $method;
    }
}
