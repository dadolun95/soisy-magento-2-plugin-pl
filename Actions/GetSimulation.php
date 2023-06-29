<?php
namespace Soisy\PaymentMethod\Actions;

use Soisy\PaymentMethod\Helper\Settings;
use Soisy\PaymentMethod\Log\Logger;

class GetSimulation
{
    public $settings;
    public $logger;

    public function __construct(
        Settings $settings,
        Logger $logger
    ) {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Return default widget generated remotely by Pagolight API
     * @param $amount
     * @return string
     */
    public function getDefaultWidgetSimulation($amount)
    {
        $priceInMinorUnit = (int) $amount * 100;
        $minimumOrderValue = 6000;
        $maximumOrderValue = 300000;

        if ($priceInMinorUnit < $minimumOrderValue) {
            return;
        }

        if ($priceInMinorUnit > $maximumOrderValue) {
            return;
        }
        return '<div id="heidipay-container"
            class="heidipay-container-2"
            data-heidipay="true"
            data-heidipay-minorAmount="' . $priceInMinorUnit  . '"
            data-heidipay-term="12"
            data-heidipay-currencySymbol="EUR"
            data-heidipay-lang="it"
            data-heidipay-type="PRODUCT_DESCRIPTION_PAGOLIGHT"
            data-heidipay-apiKey="' . $this->settings->getShopId(true) .'"
            data-heidipay-cadence="MONTHLY"
            data-heidipay-thousandsSeparator="."
            data-heidipay-decimalSeparator=","
            data-heidipay-symbolOnLeft="false"
            data-heidipay-spaceBetweenAmountAndSymbol="true"
            data-heidipay-decimalDigits="2">
        </div><script src="https://upstream.heidipay.com/sdk/heidi-upstream-lib.js"></script>';
    }

    public function execute(float $amount)
    {
        if (!$this->settings->isActive() || !$this->settings->showSimulation()) {
            return false;
        }

        if ($amount < $this->settings->getMinAmount()) {
            return false;
        }

        /*
         * DEFAULT WIDGET SIMULATION
         * */
        return $this->getDefaultWidgetSimulation($amount);

    }
}
