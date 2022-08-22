<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Adminhtml\Carrier\ChazkiRates;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiRegular;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\ChazkiRegularCollection;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\ChazkiRegularCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;

class GridChazkiRegular extends GridChazki
{
    /**
     * @var ChazkiRegular
     */
    protected $_chazkiRegularRate;

    /**
     * @var ChazkiRegularCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * GridChazkiRegular constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ChazkiRegularCollectionFactory $collectionFactory
     * @param ChazkiRegular $chazkiRegularRate
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ChazkiRegularCollectionFactory $collectionFactory,
        ChazkiRegular $chazkiRegularRate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_chazkiRegularRate = $chazkiRegularRate;

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
        $this->setId('shippingChazkiRegularRateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return GridChazkiRegular
     */
    protected function _prepareCollection()
    {
        /** @var $collection ChazkiRegularCollection */
        $collection = $this->_collectionFactory->create();
        $collection->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
