<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */
namespace Chazki\ChazkiArg\Model\Config\Backend;

use Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiExpressFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class ChazkiExpress extends Value
{
    /**
     * @var ChazkiExpressFactory
     */
    protected $chazkiExpressFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ChazkiExpressFactory $chazkiExpressFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ChazkiExpressFactory $chazkiExpressFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->chazkiExpressFactory = $chazkiExpressFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value
     * @throws LocalizedException
     */
    public function afterSave()
    {
        /** @var \Chazki\ChazkiArg\Model\ResourceModel\Carrier\ChazkiExpress $chazkiExpress */
        $chazkiExpress = $this->chazkiExpressFactory->create();
        $chazkiExpress->uploadAndImport($this);

        return parent::afterSave();
    }
}
