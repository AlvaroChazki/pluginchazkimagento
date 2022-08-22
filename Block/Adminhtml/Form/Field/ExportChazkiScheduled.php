<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class ExportChazkiScheduled extends AbstractElement
{
    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock(
            Button::class
        );

        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];

        $url = $this->_backendUrl->getUrl("*/*/exportChazkiScheduledRates", $params);
        $data = [
            'label' => __('Export CSV'),
            'onclick' => "setLocation('" . $url . "')",
            'class' => ''
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }
}
