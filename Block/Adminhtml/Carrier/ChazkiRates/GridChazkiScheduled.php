<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Adminhtml\Carrier\ChazkiRates;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiScheduled;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\ChazkiScheduledCollection;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\ChazkiScheduledCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;

class GridChazkiScheduled extends GridChazki
{
    /**
     * @var ChazkiScheduled
     */
    protected $_chazkiScheduledRate;

    /**
     * @var ChazkiScheduledCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * GridChazkiScheduled constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ChazkiScheduledCollectionFactory $collectionFactory
     * @param ChazkiScheduled $chazkiScheduledRate
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ChazkiScheduledCollectionFactory $collectionFactory,
        ChazkiScheduled $chazkiScheduledRate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_chazkiScheduledRate = $chazkiScheduledRate;

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
        $this->setId('shippingChazkiScheduledRateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return GridChazkiScheduled
     */
    protected function _prepareCollection()
    {
        /** @var $collection ChazkiScheduledCollection */
        $collection = $this->_collectionFactory->create();
        $collection->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
