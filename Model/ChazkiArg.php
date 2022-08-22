<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

namespace Chazki\ChazkiArg\Model;

use Chazki\ChazkiArg\Helper\Data as HelperData;
use Chazki\ChazkiArg\Model\Connect as ApiConnect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class ChazkiArg
{
    const TRACKING_CODE = 'chazki_arg';
    const TRACKING_LABEL = 'Chazki';

    /**
     * @var ApiConnect
     */
    protected Connect $connect;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var TrackFactory
     */
    protected TrackFactory $trackFactory;

    /**
     * @var TrackCollectionFactory
     */
    protected TrackCollectionFactory $trackCollectionFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected ProductMetadataInterface $productMetadata;

    /**
     * @var Http
     */
    protected Http $request;

    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * ChazkiArg constructor.
     * @param Connect $connect
     * @param HelperData $helperData
     * @param OrderRepositoryInterface $orderRepository
     * @param TrackFactory $trackFactory
     * @param TrackCollectionFactory $trackCollectionFactory
     * @param ProductMetadataInterface $productMetadata
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ApiConnect               $connect,
        HelperData               $helperData,
        OrderRepositoryInterface $orderRepository,
        TrackFactory             $trackFactory,
        TrackCollectionFactory   $trackCollectionFactory,
        ProductMetadataInterface $productMetadata,
        Http                     $request,
        ScopeConfigInterface     $scopeConfig
    )
    {
        $this->connect = $connect;
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
        $this->trackFactory = $trackFactory;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->productMetadata = $productMetadata;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $shipping
     * @return bool
     * @throws Zend_Log_Exception
     */
    public function createShipment($shipping)
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__);

        if (!$this->helperData->getEnabled()) {
            return false;
        }

        $shippingAddress = $shipping->getShippingAddress();
        $items = $shipping->getItems();
        $order = $shipping->getOrder();

        if (!isset($shippingAddress) || !isset($items) || !count($items) || !isset($order)) {
            return false;
        }

        $shippingMethod = $order->getShippingMethod(true);
        $shipmentAmount = $order->getShippingAmount();
        $carrierMethod = $shippingMethod->getMethod();
        $shipmentType = '';
        $logger->info(__METHOD__ . "-" . __LINE__ . ' $carrierMethod ' . json_encode($carrierMethod));
        $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingAddressData ' . json_encode($shipmentAmount));

        if (strpos($carrierMethod, 'reg') !== false) {
            $shipmentType = 'Regular';
        } elseif (strpos($carrierMethod, 'exp') !== false) {
            $shipmentType = 'Express';
        } elseif (strpos($carrierMethod, 'sche') !== false) {
            $shipmentType = 'Programado';
        }

        // ARGENTINA    CHILE       COLOMBIA        NEXICO      PERU
        //  es_AR       es_CL       es_CO          -es_MX      -es_PE
        $countryMagento = $this->scopeConfig->getValue('general/locale/code', $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        $packageSizeIdParcial = '';


        switch ($countryMagento) {
            case 'es_AR':
                $packageSizeIdParcial = 'L_ARG';
                break;
            case 'es_CL':
                $packageSizeIdParcial = 'M';
                break;
            case 'es_CO':
                $packageSizeIdParcial = 'CAJA20KG';
                break;
            case 'es_MX':
                $packageSizeIdParcial = 'M';
                break;
            case 'es_PE':
                $packageSizeIdParcial = 'M';
                break;
        }

        $packages = [];
        $compactNamePackages = '';
        $compactWeightPackages = 0;
        $compactQuantityPackages = 0;
        $compactPricePackages = 0;

        /** @var Item $item */
        foreach ($items as $item) {
            $product = [
                'quantity' => $item->getQty(),
                'weight' => intval($item->getWeight()),
                'unitaryProductPrice' => doubleval($item->getPrice()),
                'clientPackageID' => '68473029',
                'name' => '- ' . $item->getName(),
//                'name' => ' - (' . $item->getQty() . ') ' . $item->getName(),
                'envelope' => 'Caja',
                'weightUnit' => 'kg',
                'size' => $packageSizeIdParcial,
                'currency' => 'PEN',
            ];
            $compactNamePackages .= ' - (' . $item->getQty() . ') ' . $item->getName();
            $compactWeightPackages += intval($item->getWeight());
            $compactQuantityPackages += $item->getQty();
            $compactPricePackages += doubleval($item->getQty() * $item->getPrice());
            $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $items ' . json_encode($item));
            $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $items ' . $item->convertToJson());
            $packages[] = $product;
        }

        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $items ' . json_encode($packages));
//        /** substr to remove the last '+' */
//        $product['name'] = substr($product['name'], 0, -2);
//        $product['description'] = substr($product['description'], 0, -2);

        $shipmentNotes = '';

        $referenceNote = $shippingAddress->{$this->helperData->getFunctionName('get', HelperData::REFERENCE_ATTRIBUTE_CODE)}();
        $rucNumbeDrop = $shippingAddress->{$this->helperData->getFunctionName('get', HelperData::RUC_NUMBER_ATTRIBUTE_CODE)}();
        $referenceAddressDrop = $shippingAddress->{$this->helperData->getFunctionName('get', HelperData::REFERENCE_ADDRESS_CODE)}();

        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $rucNumbeDrop ' . $rucNumbeDrop);

        if (isset($referenceNote) && !empty($referenceNote)) {
            $shipmentNotes .= __('Customer Reference Note') . ': ' . $referenceNote;
        }

//        $postShipment = $this->request->getPostValue('shipment');
        if (
            isset($postShipment) &&
            isset($postShipment['destination_reference_notes']) &&
            !empty($postShipment['destination_reference_notes'])
        ) {
            $shipmentNotes .= (empty($shipmentNotes) ? '' : ' | ') . __('Seller Reference Note') . ': ' . $postShipment['destination_reference_notes'];
        }

        $shipment = [
            'enterpriseKey' => $this->getEnterpriseKeyTestOrigin(), // SE PONDRA EL QUE LA EMPResa INGRESE EN EL MODULO DE CHAZKI
            'orders' => [[
                /// ESTATICOS POR MOMENTO
                'trackCode' => strtoupper(preg_replace('/\s+/', '', $this->getPrefixOrigin())) . '-' . $order->getId(),
                'paymentMethodID' => "PAGADO",
                'paymentProofID' => "BOLETA",
                'serviceID' => "SAME DAY",
                'packageEnvelope' => "Caja",
                'packageSizeID' => $packageSizeIdParcial,
                'reverseLogistic' => "NO",
                'crossdocking' => "NO",

                "pickUpLandMark" => "Tienda ABCD #0000",

                'packageWeight' => $compactWeightPackages,
                'productPrice' => $compactPricePackages,
                'packageQuantity' => $compactQuantityPackages,
                'productDescription' => $compactNamePackages,

                /* DESTINO DE RECOJO */
                'pickUpBranchID' => '',
                'pickUpAddress' => $this->getAddressOrigin(),
                'pickUpPostalCode' => $this->getZipCodeOrigin(),
                'pickUpAddressReference' => $this->getAddressReferenceOrigin(), // Referencia
                'pickUpPrimaryReference' => $this->getCityOrigin(), // Distrito
                'pickUpSecondaryReference' => $this->getRegionStateOrigin(), // Departamento
                'pickUpNotes' => $this->getNotesOrigin(),
                'pickUpContactName' => $this->getContactNameOrigin(),
                'pickUpContactPhone' => $this->getContactPhoneOrigin(),
                'pickUpContactDocumentTypeID' => 'RUC',
                'pickUpContactDocumentNumber' => $this->getRucNumberOrigin(),
                'pickUpContactEmail' => $this->getContactEmailOrigin(),

                /* DESTINO DE ENTREGA */
                'dropBranchID' => '',
                'dropAddress' => $shippingAddress->getData('street') . ', ' . $shippingAddress->getCountryId(), // Direccion
                'dropPostalCode' => $shippingAddress->getPostcode(),
                'dropAddressReference' => $referenceAddressDrop, // Referencia
                'dropPrimaryReference' => $shippingAddress->getCity(), // Distrito
                'dropSecondaryReference' => $shippingAddress->getRegionCode(), // Departamento
                'dropNotes' => $shipmentNotes,
                'dropContactName' => $shippingAddress->getName(),
                'dropContactPhone' => $shippingAddress->getTelephone(),
                'dropContactDocumentTypeID' => 'RUC',
                'dropContactDocumentNumber' => $rucNumbeDrop,
                'dropContactEmail' => $shippingAddress->getEmail(),

                "pickUpPoint" => [
                    -77.0020558,
                    -12.0868049
                ],
                "dropPoint" => [
                    -76.978899,
                    -12.085319
                ],

                'shipmentPrice' => $shipmentAmount,
                'packages' => $packages,
            ]]
        ];

        $shippingTracks = $shipping->getTracks();
        $logger->info(__METHOD__ . "-" . __LINE__ . '  ' . json_encode($shipment));

        if (isset($shippingTracks) && count($shippingTracks)) {
            foreach ($shippingTracks as $key => $track) {
//                if ($track->getCarrierCode() === self::TRACKING_CODE) {

                if ($track->getNumber() != '') {
//                    $shipment['orders'][0]['trackCode'] = strtoupper(preg_replace('/\s+/', '', $track->getTitle())) . '-' . $track->getTrackNumber();
                    $shipment['orders'][0]['trackCode'] = strtoupper(preg_replace('/\s+/', '', $this->getPrefixOrigin())) . '-' . $order->getId();
                }
//                else {
////                    $shipment['orders'][0]['trackCode'] = strtoupper(preg_replace('/\s+/', '', $this->getPrefixOrigin())) . '-' . $track->getTrackNumber();
//                    $shipment['orders'][0]['trackCode'] = strtoupper(preg_replace('/\s+/', '', $this->getPrefixOrigin())) . '-' . $order->getId();
//                }
                $keyTrackId = $key;
//                    break;
//                }
            }
        }

        $logger->info(__METHOD__ . "-" . __LINE__ . ' $shippingTracks ' . json_encode($shippingTracks));
        $logger->info(__METHOD__ . "-" . __LINE__ . ' $shipment ' . json_encode($shipment));

        $response = json_decode($this->connect->createShipment($shipment), true);

        $logger->info(__METHOD__ . "-" . __LINE__ . ' El response es : ' . json_encode($response));

//        if (isset($response['message']) && isset($response['data'])) {
//            $msg = 'Error while sending Chazki shipment.';
//            $order->addCommentToStatusHistory($msg);
//            $this->orderRepository->save($order);
//
//            $this->helperData->log('Shipment for order #' . $order->getIncrementId() . ' error while sending to Chazki.');
//            $this->helperData->log(print_r($response, true));
//
//            return false;
//        } else {
//            if (isset($shipment['shipment']['tracking']) && isset($keyTrackId)) {
//                if (is_array($shippingTracks)) {
//                    // $logger->info(__METHOD__ . "-" . __LINE__ . ' EL valor del $shippingTracks ' . json_encode($shippingTracks));
//
//                    $shippingTracks[$keyTrackId]->setTrackNumber($response['shipment']['tracking']);
//
//                // $shippingTracks[$keyTrackId]->setTrackNumber($response['shipment']['tracking']);
//                } else {
//                    $shippingTracks->getItems()[$keyTrackId]->setTrackNumber($response['shipment']['tracking']);
//                }
//            } else {
//                if (version_compare($this->productMetadata->getVersion(), '2.2.2', '<')) {
//                    $shippingTracks = $shipping->getTracks();
//
//                    if (is_array($shippingTracks) && !count($shippingTracks)) {
//                        $shipping->setTracks(
//                            $this->trackCollectionFactory->create()->setShipmentFilter(
//                                $shipping->getId()
//                            )
//                        );
//                    }
//                }
//
//                $trackData = [
//                    'carrier_code' => self::TRACKING_CODE,
//                    'title' => __(self::TRACKING_LABEL),
//                    'number' => $response['shipment']['tracking']
//                ];
//
//                $track = $this->trackFactory->create()->addData($trackData);
//                $shipping->addTrack($track);
//            }
//
//            $shipping->save();
//
//            $msg = 'Chazki shipment was created successfully - Track ID: ' . $response['shipment']['tracking'];
//
//            $order->addCommentToStatusHistory($msg);
//            $this->orderRepository->save($order);
//
//            $this->helperData->log('Shipment for order #' . $order->getIncrementId() . ' sent to Chazki successfully - Track ID: ' . $response['shipment']['tracking']);
//            $this->helperData->log(print_r($response, true));
//
//            return true;
//        }
    }

    /**
     * @return string
     */
    public function getTitleTitleCarrierShippingInfo()
    {
        return $this->getPrefixOrigin();
    }

    /**
     * @param $trackingId
     * @return bool|string
     * @throws Zend_Log_Exception
     */
    public function getShipment($trackingId)
    {
        return $this->connect->getShipment($trackingId);
    }

    /**
     * Return config of module status
     *
     * @return mixed
     */
    public function isModuleEnable()
    {
        return $this->helperData->getEnabled();
    }

    /**
     * @return mixed
     */
    protected function getCountryOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/country_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getRegionStateOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/region_state_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getCityOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/city_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getAddressOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/address_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getZipCodeOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/zip_code_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getAddressReferenceOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/address_reference_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getContactNameOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/contact_name_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getContactEmailOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/contact_email_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getContactPhoneOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/contact_phone_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getContactDocumentTypeOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/contact_document_type', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getDniContactTypeDocumentOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/type_dni', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getPassportContactTypeDocumentOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/type_passport', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getNotesOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/notes_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getRucNumberOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/ruc_number_origin', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getEnterpriseKeyTestOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/api_key_testing', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getEnterpriseKeyLiveOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/api_key_live', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getPrefixOrigin()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/prefix', ScopeInterface::SCOPE_STORE);
    }

}
