<?php
namespace Soisy\PaymentMethod\Model;

class Soisy extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_SOISY_CODE = 'soisy';
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_SOISY_CODE;

    /**
     * Soisy payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Soisy\PaymentMethod\Block\Form\Soisy::class;

    /**
     * Instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        $instructions = trim($this->getConfigData('instructions'));
        return $instructions;
    }
}