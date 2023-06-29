<?php
namespace Soisy\PaymentMethod\Block\Onepage;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Soisy\PaymentMethod\Helper\Settings;

/**
 * One page checkout success page
 *
 * @api
 */
class SoisySuccess extends Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Soisy\PaymentMethod\Helper\Settings
     */
    protected $_settings;

    /**
     * @var
     */
    protected $order;

    /**
     * @var
     */
    public $paymentMethod;

    /**
     * @var
     */
    public $soisyToken;

    public function __construct(
        Template\Context $context,
        CheckoutSession $checkoutSession,
        Settings $settings,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_settings = $settings;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->_checkoutSession->getLastRealOrder();
        }
        return $this->order;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        //ToDo: if module is active in system
        //ToDo: if setting module is active
        $order = $this->getOrder();
        $this->paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if ($this->paymentMethod === 'soisy' && !empty($order->getSoisyToken())){
            $this->soisyToken = $order->getSoisyToken();
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getSoisyToken()
    {
        return $this->soisyToken;
    }

    /**
     * @return string|null
     */
    public function getLink()
    {
        $token=$this->getSoisyToken();
        if ($token) {
            $webapp = trim($this->_settings->getWebapp());
            $webapp = rtrim($webapp, '/');
            $shopId = trim($this->_settings->getShopId());
            $webappUrl = "{$webapp}/{$shopId}#/loan-request?token={$token}";
            return $webappUrl;
        }
        return null;
    }
}
