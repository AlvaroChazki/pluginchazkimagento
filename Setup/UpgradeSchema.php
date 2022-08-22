<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Setup;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Log_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        if (version_compare($context->getVersion(), '0.3.8', '>')) {
            $logger->info(__METHOD__ . "-" . __LINE__ . ' ' . version_compare($context->getVersion(), '0.3.6', '>'));

            $connection = $setup->getConnection();

            // Reference Code Field
            $connection->addColumn(
                $setup->getTable('quote_address'),
                HelperData::REFERENCE_ATTRIBUTE_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Reference Text',
                    'after' => 'fax'
                ]
            );

            $connection->addColumn(
                $setup->getTable('sales_order_address'),
                HelperData::REFERENCE_ATTRIBUTE_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Reference Text',
                    'after' => 'fax'
                ]
            );

            // Ruc Number Field
            $connection->addColumn(
                $setup->getTable('quote_address'),
                HelperData::RUC_NUMBER_ATTRIBUTE_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Ruc Number',
                    'after' => 'reference_note'
                ]
            );

            $connection->addColumn(
                $setup->getTable('sales_order_address'),
                HelperData::RUC_NUMBER_ATTRIBUTE_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Ruc Number',
                    'after' => 'reference_note'
                ]
            );

            // Reference Address Field
            $connection->addColumn(
                $setup->getTable('quote_address'),
                HelperData::REFERENCE_ADDRESS_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Reference Address Number',
                    'after' => 'reference_note'
                ]
            );

            $connection->addColumn(
                $setup->getTable('sales_order_address'),
                HelperData::REFERENCE_ADDRESS_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Reference Address Number',
                    'after' => 'reference_note'
                ]
            );
        }

        $setup->endSetup();
    }
}
