<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Import extends AbstractElement
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setType('file');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';

        $html .= '<input id="time_condition" type="hidden" name="' . $this->getName() . '" value="' . time() . '" />';

        $html .= parent::getElementHtml();

        return $html;
    }
}
