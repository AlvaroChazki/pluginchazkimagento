<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates;

/**
 * Query builder for table rate
 */
class RateQuery
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    private $request;

    /**
     * RateQuery constructor.
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $this->request = $request;
    }

    /**
     * Prepare select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    public function prepareSelect(\Magento\Framework\DB\Select $select)
    {
        $select->where(
            'website_id = :website_id'
        )->order(
            ['dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC']
        )->limit(
            1
        );

        $orWhere = '(' . implode(
            ') OR (',
            [
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode_prefix",
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",

                // Handle asterisk in dest_zip field
                "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = '*'",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = '*'",
                "dest_country_id = '0' AND dest_region_id = :region_id AND dest_zip = '*'",
                "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = '*'",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode_prefix"
            ]
        ) . ')';
        $select->where($orWhere);

        return $select;
    }

    /**
     * Returns query bindings
     *
     * @return array
     */
    public function getBindings()
    {
        $bind = [
            ':website_id' => (int)$this->request->getWebsiteId(),
            ':country_id' => $this->request->getDestCountryId(),
            ':region_id' => (int)$this->request->getDestRegionId(),
            ':postcode' => $this->request->getDestPostcode(),
            ':postcode_prefix' => $this->getDestPostcodePrefix()
        ];

        return $bind;
    }

    /**
     * Returns rate request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the entire postcode if it contains no dash or the part of it prior to the dash in the other case
     *
     * @return string
     */
    private function getDestPostcodePrefix()
    {
        if (!preg_match("/^(.+)-(.+)$/", $this->request->getDestPostcode(), $zipParts)) {
            return $this->request->getDestPostcode();
        }

        return $zipParts[1];
    }
}
