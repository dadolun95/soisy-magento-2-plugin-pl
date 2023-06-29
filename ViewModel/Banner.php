<?php
namespace Soisy\PaymentMethod\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Soisy\PaymentMethod\Actions\GetSimulation;
use Soisy\PaymentMethod\Helper\Settings as Helper;

class Banner implements ArgumentInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var GetSimulation
     */
    protected $getSimulation;

    /**
     * @var
     */
    protected $productPrice;

    /**
     * Banner constructor.
     * @param Helper $helper
     * @param GetSimulation $getSimulation
     */
    public function __construct(
        Helper $helper,
        GetSimulation $getSimulation
    ) {
        $this->helper = $helper;
        $this->getSimulation = $getSimulation;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;
        $this->productPrice = $product->getFinalPrice();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        $settings = $this->helper;
        $result = false;
        if ($settings->isActive()) {
            $minImport = $settings->getMinAmount();
            $product = $this->getProduct();
            $finalPrice = $product->getFinalPrice();
            if ($finalPrice >= $minImport) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $label = $this->helper->getFrontendDescription();
        if ($this->helper->getPromoAllowed() && !empty($this->helper->getPromoDescription())) {
            $label = $this->helper->getPromoDescription();
        }
        return $label;
    }

    /**
     * @return false|string
     */
    public function getSimulation()
    {
        return $this->getSimulation->execute($this->productPrice);
    }

    /**
     * @return string|string[]
     */
    public function getPopupContent()
    {
        return $this->helper->getFrontendPopup();
    }

    /**
     * @return string
     */
    public function getIsSaleableClass()
    {
        return $this->getProduct()->isSaleable() ? 'saleable' : 'not-saleable';
    }
}
