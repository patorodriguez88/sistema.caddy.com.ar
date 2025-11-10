<?php
class OscommerceSoapClient
{
    private $client;
    private $apiKey;

    public function __construct($soapUrl, $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new SoapClient($soapUrl, [
            'trace' => false,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'encoding' => 'utf-8'
        ]);
    }

    private function withAuthHeader($params = [])
    {
        // Header SOAP con api_key, como indica la wiki
        $authXml = new SoapVar(
            '<auth><api_key>' . htmlspecialchars($this->apiKey) . '</api_key></auth>',
            XSD_ANYXML
        );
        $header = new SoapHeader('urn:Platformwsdl', 'auth', $authXml, false);
        $this->client->__setSoapHeaders([$header]);
        return $params;
    }

    // Ejemplos de métodos: ajustá los nombres a lo que exponga tu WSDL
    public function getOrder($orderId)
    {
        $params = $this->withAuthHeader(['orderId' => (int)$orderId]);
        return $this->client->__soapCall('getOrder', [$params]);
    }

    public function listOrders($updatedFrom = null, $updatedTo = null, $status = null)
    {
        // algunos WSDL usan filtros tipo date_from/date_to/status
        $filters = [];
        if ($updatedFrom) $filters['updatedFrom'] = $updatedFrom; // 'Y-m-d H:i:s'
        if ($updatedTo)   $filters['updatedTo']   = $updatedTo;
        if ($status)      $filters['status']      = $status;      // e.g. 'paid','processing'
        $params = $this->withAuthHeader($filters);
        return $this->client->__soapCall('listOrders', [$params]);
    }
}
