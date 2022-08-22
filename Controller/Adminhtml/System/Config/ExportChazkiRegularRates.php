<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Controller\Adminhtml\System\Config;

use Chazki\ChazkiArg\Block\Adminhtml\Carrier\ChazkiRates\GridChazkiExpress;
use Chazki\ChazkiArg\Block\Adminhtml\Carrier\ChazkiRates\GridChazkiRegular;
use Magento\Config\Controller\Adminhtml\System\AbstractConfig;
use Magento\Framework\App\ResponseInterface;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportChazkiRegularRates extends AbstractConfig
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export shipping chazki rates in csv format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'chazki-regular-rates.csv';
        /** @var $gridBlock GridChazkiExpress */
        $gridBlock = $this->_view->getLayout()->createBlock(
            GridChazkiRegular::class
        );

        $websiteId = $this->getRequest()->getParam('website');

        if ($websiteId === null || $websiteId === '') {
            $websiteId = 0;
        }

        $website = $this->_storeManager->getWebsite($websiteId);
        $gridBlock->setWebsiteId($website->getId());
        $content = $gridBlock->getCsvFile();
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
