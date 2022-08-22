<?php

namespace Chazki\ChazkiArg\Block;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\View\Element\Template;

class ReferenceAddress extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Chazki_ChazkiArg::checkout/form/reference_address.phtml';

    /**
     * @var AddressInterface
     */
    protected AddressInterface $address;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * ReferenceAddress constructor.
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
    public function getReferenceAddressValue()
    {
        $referenceAddressValue = $this->getAddress()->getCustomAttribute(HelperData::REFERENCE_ADDRESS_CODE);

        if (!$referenceAddressValue instanceof AttributeInterface) {
            return '';
        }

        return $referenceAddressValue->getValue();
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
