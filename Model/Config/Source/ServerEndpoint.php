<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ServerEndpoint implements OptionSourceInterface
{
    const LIVE_SERVER_ENDPOINT = '1';
    const TESTING_SERVER_ENDPOINT = '0';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LIVE_SERVER_ENDPOINT, 'label' => __('Live')],
            ['value' => self::TESTING_SERVER_ENDPOINT, 'label' => __('Testing')]
        ];
    }
}
