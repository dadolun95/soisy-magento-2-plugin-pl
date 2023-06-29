<?php
namespace Soisy\PaymentMethod\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Soisy\PaymentMethod\Helper\Settings;

class SoisyConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Soisy::PAYMENT_METHOD_SOISY_CODE;

    /**
     * @var Settings
     */
    protected $_soisySettings;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        Escaper $escaper,
        Settings $soisySettings
    ) {
        $this->escaper = $escaper;
        $this->_soisySettings = $soisySettings;
    }

    public function getSimulation()
    {
        $objectManager = ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $quote = $cart->getQuote();
        $quoteGrandTotal = $quote->getGrandTotal();
        $simulationHtml = $objectManager->get('\Soisy\PaymentMethod\Actions\GetSimulation')->execute($quoteGrandTotal);
        return $simulationHtml;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        $config['payment']['instructions'][$this->methodCode] = $this->getInstructions();
        $config['payment']['simulation'][$this->methodCode] = $this->getSimulation();
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions()
    {
        return nl2br($this->escaper->escapeHtml($this->_soisySettings->getInstructions()));
    }
}
