<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Address\Edit\Field;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\View\Element\Template;

class ReferenceNote extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Chazki_ChazkiArg::address/edit/field/reference_note.phtml';

    /**
     * @var AddressInterface
     */
    protected AddressInterface $address;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * ReferenceNote constructor.
     * @param Template\Context $context
     * @param AddressInterface $address
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AddressInterface $address,
        HelperData       $helperData,
        array            $data = []
    ) {
        parent::__construct($context, $data);

        $this->helperData = $helperData;
    }

    /**
     * @return mixed|string
     */
    public function getReferenceNoteValue()
    {
        $referenceNoteValue = $this->getAddress()->getCustomAttribute(HelperData::REFERENCE_ATTRIBUTE_CODE);

        if (!$referenceNoteValue instanceof AttributeInterface) {
            return '';
        }

        return $referenceNoteValue->getValue();
    }

    /**
     * Return the associated address.
     *
     * @return AddressInterface
     */
    public function getAddress(): AddressInterface
    {
        return $this->address;
    }

    /**
     * Set the associated address.
     *
     * @param AddressInterface $address
     */
    public function setAddress(AddressInterface $address)
    {
        $this->address = $address;
    }
}
