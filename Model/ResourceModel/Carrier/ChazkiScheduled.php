<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\ResourceModel\Carrier;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\Import;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\RateQuery;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\RateQueryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\Filesystem\Directory\PathValidator;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Chazki\ChazkiArg\Model\Carrier\ChazkiScheduled as ChazkiScheduledModel;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class ChazkiScheduled extends AbstractDb
{
    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = [];

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * @var ScopeConfigInterface
     * @since 100.1.0
     */
    protected $coreConfig;

    /**
     * @var LoggerInterface
     * @since 100.1.0
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * @var ChazkiScheduledModel 
     */
    protected $carrierChazkiScheduled;

    /**
     * @var DriverPool
     */
    protected $driverPool;

    /**
     * @var Import
     */
    private $import;

    /**
     * @var RateQueryFactory
     */
    private $rateQueryFactory;

    /**
     * ChazkiScheduled constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $coreConfig
     * @param StoreManagerInterface $storeManager
     * @param ChazkiScheduledModel $carrierChazkiScheduled
     * @param DriverPool $driverPool
     * @param Import $import
     * @param RateQueryFactory $rateQueryFactory
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ScopeConfigInterface $coreConfig,
        StoreManagerInterface $storeManager,
        \Chazki\ChazkiArg\Model\Carrier\ChazkiScheduled $carrierChazkiScheduled,
        DriverPool $driverPool,
        Import $import,
        RateQueryFactory $rateQueryFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->coreConfig = $coreConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->carrierChazkiScheduled = $carrierChazkiScheduled;
        $this->driverPool = $driverPool;
        $this->import = $import;
        $this->rateQueryFactory = $rateQueryFactory;
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shipping_chazki_arg_scheduled', 'pk');
    }

    /**
     * Return table rate array or false by rate request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable());
        /** @var RateQuery $rateQuery */
        $rateQuery = $this->rateQueryFactory->create(['request' => $request]);

        $rateQuery->prepareSelect($select);
        $bindings = $rateQuery->getBindings();

        $result = $connection->fetchRow($select, $bindings);

        if (!$result) {
            $bindings[':website_id'] = 0;
            $result = $connection->fetchRow($select, $bindings);
        }

        // Normalize destination zip code
        if ($result && $result['dest_zip'] == '*') {
            $result['dest_zip'] = '';
        }

        return $result;
    }

    /**
     * @param array $fields
     * @param array $values
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */

    /**
     * @param $path
     * @param string $driverCode
     * @return Read
     */
    public function getDirectoryReadByPath($path, $driverCode = DriverPool::FILE)
    {
        $driver = $this->driverPool->getDriver($driverCode);
        $factory = new \Magento\Framework\Filesystem\File\ReadFactory(
            $this->driverPool
        );

        return new Read(
            $factory,
            $driver,
            $path,
            new PathValidator($driver)
        );
    }
    
    private function importData(array $fields, array $values)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if (count($fields) && count($values)) {
                $this->getConnection()->insertArray($this->getMainTable(), $fields, $values);
                $this->_importedRows += count($values);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to import data'), $e);
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        }
        $connection->commit();
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiScheduled
     * @todo: this method should be refactored as soon as updated design will be provided
     * @see https://wiki.corp.x.com/display/MCOMS/Magento+Filesystem+Decisions
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($_FILES['groups']['tmp_name']['chazkiargscheduled']['fields']['import']['value'])) {
            return $this;
        }
        $filePath = $_FILES['groups']['tmp_name']['chazkiargscheduled']['fields']['import']['value'];

        $websiteId = $this->storeManager->getWebsite($object->getScopeId())->getId();

        $file = $this->getCsvFile($filePath);
        try {
            // delete old data by website
            $condition = [
                'website_id = ?' => $websiteId
            ];
            $this->deleteByCondition($condition);

            $columns = $this->import->getColumns();
            foreach ($this->import->getData($file, $websiteId) as $bunch) {
                $this->importData($columns, $bunch);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        } finally {
            $file->close();
        }

        if ($this->import->hasErrors()) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->import->getErrors())
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }
    }

    /**
     * @param $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    private function getCsvFile($filePath)
    {
        $pathInfo = pathinfo($filePath);
        $dirName = isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
        $fileName = isset($pathInfo['basename']) ? $pathInfo['basename'] : '';

        $directoryRead = $this->getDirectoryReadByPath($dirName);

        return $directoryRead->openFile($fileName);
    }

    /**
     * @param array $condition
     * @return $this
     * @throws LocalizedException
     */
    private function deleteByCondition(array $condition)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable(), $condition);
        $connection->commit();
        return $this;
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return \Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiScheduled
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_zip',
                'price'
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }
}
