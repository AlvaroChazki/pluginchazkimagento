<?php

namespace Chazki\ChazkiArg\Block;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\View\Element\Template;

class RucNumber extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Chazki_ChazkiArg::checkout/form/ruc_number.phtml';

    /**
     * @var AddressInterface
     */
    protected AddressInterface $address;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * RucNumber constructor.
     * @param Template\Context $context
     * @param AddressInterface $address
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AddressInterface $address,
        HelperData $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helperData = $helperData;
    }

    /**
     * @return mixed|string
     */
    public function getRucNumberValue()
    {
        $rucNumberValue = $this->getAddress()->getCustomAttribute(HelperData::RUC_NUMBER_ATTRIBUTE_CODE);

        if (!$rucNumberValue instanceof AttributeInterface) {
            return '';
        }

        return $rucNumberValue->getValue();
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
