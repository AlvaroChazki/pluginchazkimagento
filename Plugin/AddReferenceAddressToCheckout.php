<?php

namespace Chazki\ChazkiArg\Plugin;

use Chazki\ChazkiArg\Block\ReferenceAddress;
use Chazki\ChazkiArg\Helper\Data as HelperData;
use Magento\Customer\Block\Address\Edit as Subject;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class AddReferenceAddressToCheckout
{
    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * AddReferenceAddressToCheckout constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Subject $subject
     * @param $html
     * @return string
     * @throws Zend_Log_Exception
     */
    public function afterToHtml(Subject $subject, $html): string
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        if ($this->helperData->getEnabled()) {
            $referenceAddressBloc = $this->getChildBlock(ReferenceAddress::class, $subject);
            $referenceAddressBloc->setAddress($subject->getAddress());
            $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $referenceNoteBlock ' . json_encode($referenceAddressBloc));
            $html = $this->appendBlockBeforeFieldsetEnd($html, $referenceAddressBloc->toHtml());
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $childHtml
     *
     * @return string
     */
    private function appendBlockBeforeFieldsetEnd(string $html, string $childHtml): string
    {
        $pregMatch = '/\<\/fieldset\>/';
        $pregReplace = $childHtml . '\0';

        return preg_replace($pregMatch, $pregReplace, $html, 1);
    }

    /**
     * @param $blockClass
     * @param $parentBlock
     *
     * @return mixed
     */
    private function getChildBlock($blockClass, $parentBlock)
    {
        return $parentBlock->getLayout()->createBlock($blockClass, basename($blockClass));
    }
}
