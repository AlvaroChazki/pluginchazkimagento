<?php
/**
 * Copyright © 2022 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ContactDocumentType implements OptionSourceInterface
{
    const DNI_CONTACT_DOCUMENT_TYPE = '0';
    const PASSPORT_CONTACT_DOCUMENT_TYPE = '1';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DNI_CONTACT_DOCUMENT_TYPE, 'label' => __('DNI')],
            ['value' => self::PASSPORT_CONTACT_DOCUMENT_TYPE, 'label' => __('Passport')]
        ];
    }
}
