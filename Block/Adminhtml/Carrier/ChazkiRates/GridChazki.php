<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Adminhtml\Carrier\ChazkiRates;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;

class GridChazki extends Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * GridChazki constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Prepare table columns
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );

        $this->addColumn(
            'dest_zip',
            ['header' => __('Zip/Postal Code'), 'index' => 'dest_zip', 'default' => '*']
        );

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        return parent::_prepareColumns();
    }
}
