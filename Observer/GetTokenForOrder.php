<?php

namespace Soisy\PaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;

class GetTokenForOrder implements ObserverInterface
{
    protected $_request;

    protected $settings;

    protected $logger;

    /**
     * GetTokenForOrders constructor.
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http  $request,
        \Soisy\PaymentMethod\Helper\Settings $settings,
        \Soisy\PaymentMethod\Log\Logger      $logger
    )
    {
        $this->_request = $request;
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod === \Soisy\PaymentMethod\Model\Soisy::PAYMENT_METHOD_SOISY_CODE) {
            $this->soisyOrderCreate($order);
        }
    }

    public function soisyOrderCreate($order)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();
        $this->logger->log('notice', "soisyOrderCreate: paymenth method: $payment");

        if ($payment != 'soisy') {
            $this->logger->log('notice', "pagolightOrderCreate: payment is not pagolight, skip");
            return;
        }

        if ($order->getSoisyToken()) {
            $this->logger->log('notice', "pagolightOrderCreate: token already exists, skip");
            return;
        }

        if ($this->settings->isSandbox()) {
            $this->logger->log('notice', "pagolightOrderCreate: SANDBOX MODE.");
        }

        $incrementId = $order->getIncrementId();
        $amount = $order->getGrandTotal();

        $this->logger->log('notice', " incrementId: $incrementId");
        $this->logger->log('notice', " amount: $amount");

        $amount_cent = round($amount * 100);

        $errorUrl = $this->settings->getCmsErrorUrl();

        $successUrl = $this->settings->getCmsSuccessUrl();

        $customerEmail = $order->getData('customer_email');
        $customerFirstname = $order->getData('customer_firstname');
        $customerLastname = $order->getData('customer_lastname');

        $postdata = [
            'firstname' => $customerFirstname,
            'lastname' => $customerLastname,
            'email' => $customerEmail,
            'amount' => $amount_cent,
            'successUrl' => $successUrl,
            'errorUrl' => $errorUrl,
            'orderReference' => $incrementId
        ];

        /*
         * Generate a random unique email and a generic orderReference for sandbox customer.
         * */
        if ($this->settings->isSandbox()) {
            $postdata['email'] = 'pagolightsandbox' . date('YmdHis') . '@example.com';
            $postdata['orderReference'] = 'SOISY-SANDBOX-' . $incrementId;
        }

        foreach ($postdata as $k => $v) {
            $this->logger->log('notice', " postdata $k: $v");
        }

        $token = $this->getSoisyToken($postdata);

        if ($token === false) {
            $this->logger->log('notice', " token: FALSE");
            $this->logger->log('notice', "pagolightOrderCreate: end");
            return;
        }
        $this->logger->log('notice', "token: $token");

        $webapp = trim($this->settings->getWebapp());
        $webapp = rtrim($webapp, '/');
        $shopId = trim($this->settings->getShopId());
        $webappUrl = "{$webapp}/{$shopId}#/loan-request?token={$token}";


        $this->logger->log('notice', "webappUrl: $webappUrl");

        $endpoint = trim($this->settings->getEndpoint());
        $endpoint = rtrim($endpoint, '/');
        $orderUrl = "{$endpoint}/api/shops/{$shopId}/orders/$token";

        //$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_HOLDED, Mage::helper('soisy')->__('Customer was redirected to Pagolight'));
        $stringSoisyToken = "Token Pagolight";
        $stringCustomerWebappUrl = "Link avvio processo cliente su pagolight";
        $stringSoisyOrderInfo = "Dati json associazione ordine (per debug)";
        $order->addStatusHistoryComment("
<b>$stringSoisyToken:</b> $token <br>\n
<b>$stringCustomerWebappUrl:</b> <a target='_blank' href='$webappUrl' >$webappUrl</a><br/>\n
<b>$stringSoisyOrderInfo:</b> <a target='_blank' href='$orderUrl' >$orderUrl</a>  ")
            ->setIsCustomerNotified(false);

        $order->setSoisyToken($token);

        $order->save();
        $this->logger->log('notice', "pagolightOrderCreate: end");
    }

    protected function getSoisyToken($postdata)
    {

        $shopId = trim($this->settings->getShopId());
        $authToken = trim($this->settings->getAuthToken());
        $endpoint = trim($this->settings->getEndpoint());
        $endpoint = rtrim($endpoint, '/');
        $soisyUrl = "{$endpoint}/api/shops/{$shopId}/orders";
        $this->logger->log('notice', " shopId:$shopId - authToken:$authToken - pagolightUrl:$soisyUrl");

        $postquery = http_build_query($postdata);
        $context = stream_context_create([
            'http' => ['method' => 'POST',
                'timeout' => 5.0,
                'header' => "X-Auth-Token: $authToken\r\nContent-Type: application/x-www-form-urlencoded",
                'content' => $postquery]
        ]);
        //$this->logger->log('notice', " pagolightUrl: $soisyUrl ");
        //$result = file_get_contents($soisyUrl,false,$context);
        $result = $this->curl_get_file_contents($soisyUrl, $authToken, $postdata);

        //$response=print_r($http_response_header,true);
        //$this->logger->log('debug', "http_response_header:".$response);

        if ($result === false) {
            $this->logger->log('debug', 'result false');
            return false;
        }
        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log('debug', 'result is not a valid json');
            $this->logger->log('debug', 'result: ' . print_r($result, true));
        }
        $data = json_decode($result, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log('debug', 'result is not a valid json');
            $this->logger->log('debug', 'result: ' . print_r($result, true));
        }

        if (!is_array($data)) {
            $this->logger->log('debug', 'data is not an array ');
            return false;
        }
        if (empty($data['token'])) {
            $errors = $data['errors'];
            $this->logger->log('notice', ' Errors: ' . $errors);
            return false;
        }
        $token = $data['token'];
        return $token;
    }

    protected function curl_get_file_contents($URL, $authToken, $postdata)
    {
        $postdata = http_build_query($postdata);
        $this->logger->log('notice', "begin curl_get_file_contents ");
        $headers = [];
        $headers[] = "X-Auth-Token: $authToken";
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $contents = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            $this->logger->log('notice', "error: " . $e->getMessage());
        }

        $this->logger->log('notice', "end curl_get_file_contents ");
        if ($contents) {
            return $contents;
        }
        return FALSE;
    }

}
