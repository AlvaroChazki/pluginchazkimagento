<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Adminhtml\Carrier\ChazkiRates;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiExpress;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\ChazkiExpressCollection;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\ChazkiExpressCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;

class GridChazkiExpress extends GridChazki
{
    /**
     * @var ChazkiExpress
     */
    protected $_chazkiExpressRate;

    /**
     * @var ChazkiExpressCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * GridChazkiExpress constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ChazkiExpressCollectionFactory $collectionFactory
     * @param ChazkiExpress $chazkiExpressRate
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ChazkiExpressCollectionFactory $collectionFactory,
        ChazkiExpress $chazkiExpressRate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_chazkiExpressRate = $chazkiExpressRate;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingChazkiExpressRateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return GridChazkiExpress
     */
    protected function _prepareCollection()
    {
        /** @var $collection ChazkiExpressCollection */
        $collection = $this->_collectionFactory->create();
        $collection->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
