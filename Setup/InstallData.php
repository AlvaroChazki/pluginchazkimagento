<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Setup;

use Magento\Directory\Helper\Data;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var Data
     */
    protected $directoryData;

    /**
     * InstallData constructor.
     * @param Data $directoryData
     */
    public function __construct(Data $directoryData)
    {
        $this->directoryData = $directoryData;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [

            // ARGENTINA
//            ['country_id' => 'AR', 'code' => 'BA',   'default_name' => 'Buenos Aires'],
//            ['country_id' => 'AR', 'code' => 'CABA', 'default_name' => 'Ciudad Autónoma de Buenos Aires'],
//            ['country_id' => 'AR', 'code' => 'CT',   'default_name' => 'Catamarca'],
//            ['country_id' => 'AR', 'code' => 'CC',   'default_name' => 'Chaco'],
//            ['country_id' => 'AR', 'code' => 'CH',   'default_name' => 'Chubut'],
//            ['country_id' => 'AR', 'code' => 'CD',   'default_name' => 'Córdoba'],
//            ['country_id' => 'AR', 'code' => 'CR',   'default_name' => 'Corrientes'],
//            ['country_id' => 'AR', 'code' => 'ER',   'default_name' => 'Entre Ríos'],
//            ['country_id' => 'AR', 'code' => 'FO',   'default_name' => 'Formosa'],
//            ['country_id' => 'AR', 'code' => 'JY',   'default_name' => 'Jujuy'],
//            ['country_id' => 'AR', 'code' => 'LP',   'default_name' => 'La Pampa'],
//            ['country_id' => 'AR', 'code' => 'LR',   'default_name' => 'La Rioja'],
//            ['country_id' => 'AR', 'code' => 'MZ',   'default_name' => 'Mendoza'],
//            ['country_id' => 'AR', 'code' => 'MN',   'default_name' => 'Misiones'],
//            ['country_id' => 'AR', 'code' => 'NQ',   'default_name' => 'Neuquén'],
//            ['country_id' => 'AR', 'code' => 'RN',   'default_name' => 'Río Negro'],
//            ['country_id' => 'AR', 'code' => 'SA',   'default_name' => 'Salta'],
//            ['country_id' => 'AR', 'code' => 'SJ',   'default_name' => 'San Juan'],
//            ['country_id' => 'AR', 'code' => 'SL',   'default_name' => 'San Luis'],
//            ['country_id' => 'AR', 'code' => 'SC',   'default_name' => 'Santa Cruz'],
//            ['country_id' => 'AR', 'code' => 'SF',   'default_name' => 'Santa Fe'],
//            ['country_id' => 'AR', 'code' => 'SE',   'default_name' => 'Santiago del Estero'],
//            ['country_id' => 'AR', 'code' => 'TF',   'default_name' => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur'],
//            ['country_id' => 'AR', 'code' => 'TM',   'default_name' => 'Tucumán']


            // PERU
            /*
            ['country_id' => 'PE', 'code' => 'PE-AMA',   'default_name' => 'Amazonas'],
            ['country_id' => 'PE', 'code' => 'PE-ANC', 'default_name' => 'Ancash'],
            ['country_id' => 'PE', 'code' => 'PE-APU',   'default_name' => 'Apurímac'],
            ['country_id' => 'PE', 'code' => 'PE-ARE',   'default_name' => 'Arequipa'],
            ['country_id' => 'PE', 'code' => 'PE-AYA',   'default_name' => 'Ayacucho'],
            ['country_id' => 'PE', 'code' => 'PE-CAJ',   'default_name' => 'Cajamarca'],
            ['country_id' => 'PE', 'code' => 'PE-CUS',   'default_name' => 'Cusco'],
            ['country_id' => 'PE', 'code' => 'PE-CAL',   'default_name' => 'El Callao'],
            ['country_id' => 'PE', 'code' => 'PE-HUV',   'default_name' => 'Huancavelica'],
            ['country_id' => 'PE', 'code' => 'PE-HUC',   'default_name' => 'Huánuco'],
            ['country_id' => 'PE', 'code' => 'PE-ICA',   'default_name' => 'Ica'],
            ['country_id' => 'PE', 'code' => 'PE-JUN',   'default_name' => 'Junín'],
            ['country_id' => 'PE', 'code' => 'PE-LAL',   'default_name' => 'La Libertad'],
            ['country_id' => 'PE', 'code' => 'PE-LAM',   'default_name' => 'Lambayeque'],
            ['country_id' => 'PE', 'code' => 'PE-LIM',   'default_name' => 'Lima'],
            ['country_id' => 'PE', 'code' => 'PE-LOR',   'default_name' => 'Loreto'],
            ['country_id' => 'PE', 'code' => 'PE-MDD',   'default_name' => 'Madre de Dios'],
            ['country_id' => 'PE', 'code' => 'PE-MOQ',   'default_name' => 'Moquegua'],
            ['country_id' => 'PE', 'code' => 'PE-LMA',   'default_name' => 'Municipalidad Metropolitana de Lima'],
            ['country_id' => 'PE', 'code' => 'PE-PAS',   'default_name' => 'Pasco'],
            ['country_id' => 'PE', 'code' => 'PE-PIU',   'default_name' => 'Piura'],
            ['country_id' => 'PE', 'code' => 'PE-PUN',   'default_name' => 'Puno'],
            ['country_id' => 'PE', 'code' => 'PE-SAM',   'default_name' => 'San Martín'],
            ['country_id' => 'PE', 'code' => 'PE-TAC',   'default_name' => 'Tacna'],
            ['country_id' => 'PE', 'code' => 'PE-TUM',   'default_name' => 'Tumbes'],
            ['country_id' => 'PE', 'code' => 'PE-UCA',   'default_name' => 'Ucayali']
            */
        ];

        foreach ($data as $row) {
            $bind = [
                'country_id' => $row['country_id'],
                'code' => $row['code'],
                'default_name' => $row['default_name']
            ];

            $setup->getConnection()->insert(
                $setup->getTable('directory_country_region'),
                $bind
            );

            $regionId = $setup->getConnection()->lastInsertId(
                $setup->getTable('directory_country_region')
            );

            $bind = [
                'locale' => 'en_US',
                'region_id' => $regionId,
                'name' => $row['default_name']
            ];

            $setup->getConnection()->insert(
                $setup->getTable('directory_country_region_name'),
                $bind
            );
        }
    }
}
