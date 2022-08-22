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
use Exception;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Zend_Log;
use Zend_Log_Writer_Stream;
use Zend_Validate_Exception;

/**
 * Upgrade Data script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private CustomerSetupFactory $customerSetupFactory;

    /**
     * UpgradeData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     * @throws Zend_Validate_Exception|\Zend_Log_Exception
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        $logger->info(__METHOD__ . "-" . __LINE__ . " getVersion: " . $context->getVersion());
        $logger->info(__METHOD__ . "-" . __LINE__ . " version_compare: " . version_compare($context->getVersion(), '0.3.7', '>'));


        if (version_compare($context->getVersion(), '0.3.7', '>')) {
            /** @var CustomerSetup $customerSetup */
            $logger->info(__METHOD__ . "-" . __LINE__ . " entro al upgradeData");
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            // Reference Text Field
            $customerSetup->addAttribute('customer_address', HelperData::REFERENCE_ATTRIBUTE_CODE, [
                'label' => 'Reference Text',
                'input' => 'text',
                'type' => Table::TYPE_TEXT,
                'source' => '',
                'required' => false,
                'position' => 333,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'backend' => ''
            ]);

            $attribute = $customerSetup->getEavConfig()
                ->getAttribute('customer_address', HelperData::REFERENCE_ATTRIBUTE_CODE)
                ->addData(
                    [
                        'used_in_forms' => [
                            'adminhtml_customer_address',
                            'adminhtml_customer',
                            'customer_address_edit',
                            'customer_register_address',
                            'customer_address',
                        ]
                    ]
                );
            $attribute->save();

//             Ruc Number Field
            $customerSetup->addAttribute('customer_address', HelperData::RUC_NUMBER_ATTRIBUTE_CODE, [
                'label' => 'Ruc Number',
                'input' => 'text',
                'type' => Table::TYPE_TEXT,
                'source' => '',
                'required' => false,
                'position' => 343,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'backend' => ''
            ]);

            $attribute = $customerSetup->getEavConfig()
                ->getAttribute('customer_address', HelperData::RUC_NUMBER_ATTRIBUTE_CODE)
                ->addData(
                    [
                        'used_in_forms' => [
                            'adminhtml_customer_address',
                            'adminhtml_customer',
                            'customer_address_edit',
                            'customer_register_address',
                            'customer_address',
                        ]
                    ]
                );

            $attribute->save();


            // Reference Address Field
            $customerSetup->addAttribute('customer_address', HelperData::REFERENCE_ADDRESS_CODE, [
                'label' => 'Reference Address',
                'input' => 'text',
                'type' => Table::TYPE_TEXT,
                'source' => '',
                'required' => false,
                'position' => 343,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'backend' => ''
            ]);

            $attribute = $customerSetup->getEavConfig()
                ->getAttribute('customer_address', HelperData::REFERENCE_ADDRESS_CODE)
                ->addData(
                    [
                        'used_in_forms' => [
                            'adminhtml_customer_address',
                            'adminhtml_customer',
                            'customer_address_edit',
                            'customer_register_address',
                            'customer_address',
                        ]
                    ]
                );

            $attribute->save();

        }

        $setup->endSetup();
    }
}
