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
use Chazki\ChazkiArg\Model\Config\Source\ServerEndpoint;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Zend_Log;
use Zend_Log_Exception;
use Zend_Log_Writer_Stream;

class Connect
{
    /**
     * API Response Constants
     */
    const RESPONSE_STATUS_OK = 'Success';
    const RESPONSE_STATUS_ERROR = 'Error';

    /**
     * @var mixed|string
     */
    public $bearerToken = '';

    /**
     * @var mixed
     */
    protected $baseUrl;

    /**
     * @var mixed
     */
    protected $username;

    /**
     * @var mixed
     */
    protected $password;

    /**
     * @var mixed
     */
    protected $APIKey;

    /**
     * @var mixed
     */
    protected $urlServerEndpoint;

    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * Request instance
     *
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Connect constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param HelperData $helperData
     * @param Curl $curl
     * @param RequestInterface $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperData           $helperData,
        Curl                 $curl,
        RequestInterface     $request
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->request = $request;
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param bool $body
     * @return bool|string
     * @throws Zend_Log_Exception
     */
    protected function chazkiRequest($endpoint, string $method = 'GET', $body = false)
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);

        // $shippingOrder = $order->getBillingAddress()->setCo($this->getCountryOrigin());

        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $body ' . $body);

        $apiKey = $this->getAPIKey();
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $apiKey ' . json_encode($apiKey));

        if (!empty($apiKey)) {
            if (strpos($endpoint, '?') === false) {
                $apiKey = '?key=' . $apiKey;
            } else {
                $apiKey = '&key=' . $apiKey;
            }
        }
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $method ' . $method);


        $headers = [];
        if ($body) {
            $headers[] = "Content-Type: application/json";
        }
        $url = $this->getUrlServerEndpoint() . $endpoint;
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $url ' . $url);
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $url ' . $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 250);

        $result = curl_exec($ch);

        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del result ' . json_encode($result));
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del result ' . $result);

        if (curl_errno($ch)) {
            $this->helperData->log('Error:' . curl_error($ch));
        }
        curl_close($ch);

//        $result ='as';
        return $result;
    }

    /**
     * @return bool|mixed|string
     */
    public function getAPIKey()
    {
        if (is_null($this->APIKey)) {
            if ($this->getServerEndpoint() === ServerEndpoint::LIVE_SERVER_ENDPOINT) {
                $this->APIKey = $this->getLiveAPIKey();
            } elseif ($this->getServerEndpoint() === ServerEndpoint::TESTING_SERVER_ENDPOINT) {
                $this->APIKey = $this->getTestingAPIKey();
            }
        }

        return $this->APIKey;
    }

    /**
     * @return bool|mixed|string
     */
    protected function getUrlServerEndpoint()
    {
        if (is_null($this->urlServerEndpoint)) {
            if ($this->getServerEndpoint() === ServerEndpoint::LIVE_SERVER_ENDPOINT) {
                $this->urlServerEndpoint = $this->getLiveServerEndpoint();
            } elseif ($this->getServerEndpoint() === ServerEndpoint::TESTING_SERVER_ENDPOINT) {
                $this->urlServerEndpoint = $this->getTestingServerEndpoint();
            }
        }

        return $this->urlServerEndpoint;
    }

    /**
     * Send shipping info to Chazki
     *
     * @param $shipmentInfo
     *
     * @return bool|string
     * @throws Zend_Log_Exception
     */
    public function createShipment($shipmentInfo)
    {
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__ . "-" . __LINE__ . ' El valor del $shipmentInfo ' . json_encode($shipmentInfo));

        return $this->chazkiRequest(
//            '/shipment/create',
            '/uploadClientOrders',
            'POST',
            json_encode($shipmentInfo)
        );
    }

    /**
     * Get shipping info from Chazki
     *
     * @param $trackingId
     *
     * @return bool|string
     * @throws Zend_Log_Exception
     */
    public function getShipment($trackingId)
    {
        return $this->chazkiRequest(
            '/shipment/tracker/' . $trackingId,
            'GET'
        );
    }

    /**
     * @return mixed
     */
    protected function getServerEndpoint()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/server_endpoint', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getTestingServerEndpoint()
    {
//        return $this->scopeConfig->getValue('shipping/chazki_arg/url_testing', ScopeInterface::SCOPE_STORE);
        return 'https://us-central1-chazki-link-dev.cloudfunctions.net';
    }

    /**
     * @return mixed
     */
    protected function getLiveServerEndpoint()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/url_live', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getTestingAPIKey()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/api_key_testing', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    protected function getLiveAPIKey()
    {
        return $this->scopeConfig->getValue('shipping/chazki_arg/api_key_live', ScopeInterface::SCOPE_STORE);
    }
}
