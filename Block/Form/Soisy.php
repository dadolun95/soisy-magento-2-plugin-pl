<?php
namespace Soisy\PaymentMethod\Block\Form;

/**
 * Block for Bank Transfer payment method form
 */
class Soisy extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Soisy template
     *
     * @var string
     */
    protected $_template = 'Soisy_PaymentMethod::form/soisy.phtml';
}
