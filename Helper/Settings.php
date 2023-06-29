<?php
namespace Soisy\PaymentMethod\Helper;

use Magento\Cms\Helper\Page as CmsHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Settings extends AbstractHelper
{
    const SOISY_PAYMENT_CODE = 'soisy';
    const SOISY_SANDBOX_WEB_APP = 'https://shop.sandbox.soisy.it';
    const SOISY_SANDBOX_ENDPOINT = 'https://api.sandbox.soisy.it';
    const SOISY_SANDBOX_SHOP_ID_WIDGET_SIMULATION = 'soisytests';
    const SOISY_SANDBOX_SHOP_ID = 'partnershop';
    const SOISY_SANDBOX_X_AUTH_TOKEN = 'partnerkey';
    const SOISY_PRODUCTION_WEB_APP = 'https://shop.soisy.it';
    const SOISY_PRODUCTION_ENDPOINT = 'https://api.soisy.it';

    const XML_PATH_SETTINGS = 'payment/soisy/';

    protected $cmsHelper;
    protected $storeManager;

    public function __construct(
        Context $context,
        CmsHelper $cmsHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->cmsHelper = $cmsHelper;
        $this->storeManager = $storeManager;
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSettings($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SETTINGS . $code, $storeId);
    }

    public function isActive()
    {
        return $this->getSettings('active');
    }

    public function isSandbox()
    {
        return $this->getSettings('sandbox');
    }

    public function getMinAmount()
    {
        return $this->getSettings('min_order_total');
    }

    public function getMaxAmount()
    {
        return $this->getSettings('max_order_total');
    }

    public function getEndpoint()
    {
        if ($this->isSandbox()) {
            return self::SOISY_SANDBOX_ENDPOINT;
        }
        return self::SOISY_PRODUCTION_ENDPOINT;
    }

    public function getWebapp()
    {
        if ($this->isSandbox()) {
            return self::SOISY_SANDBOX_WEB_APP;
        }
        return self::SOISY_PRODUCTION_WEB_APP;
    }

    public function getShopId($isForWidget = false)
    {
        if ($this->isSandbox()) {
            if ($isForWidget) {
                return self::SOISY_SANDBOX_SHOP_ID_WIDGET_SIMULATION;
            } else {
                return self::SOISY_SANDBOX_SHOP_ID;
            }
        }
        return $this->getSettings('shop_id');
    }

    public function getAuthToken()
    {
        if ($this->isSandbox()) {
            return self::SOISY_SANDBOX_X_AUTH_TOKEN;
        }
        return $this->getSettings('auth_token');
    }

    public function getInstructions()
    {
        return $this->getSettings('instructions');
    }

    public function showSimulation()
    {
        return $this->getSettings('show_simulation');
    }

    public function getPromotionalRates()
    {
        return $this->getSettings('promotional_rates');
    }

    public function getZeroInterestRate()
    {
        return $this->getSettings('zero_interest_rate');
    }

    protected function getCmsUrl($urlString)
    {
        return $urlString ? $this->cmsHelper->getPageUrl($urlString) : $this->storeManager->getStore()->getBaseUrl();
    }

    public function getCmsSuccessUrl()
    {
        $url = $this->getSettings('cms_success_url');
        return $this->getCmsUrl($url);
    }

    public function getCmsErrorUrl()
    {
        $url = $this->getSettings('cms_error_url');
        return $this->getCmsUrl($url);
    }

}
