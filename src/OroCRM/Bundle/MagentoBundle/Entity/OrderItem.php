<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseOrderItem;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("orocrm_magento_order_items")
 * @ORM\Entity
 */
class OrderItem extends BaseOrderItem
{
    use OriginTrait;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="items",cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="product_type", type="string", length=255, nullable=false)
     */
    protected $productType;

    /**
     * @var string
     *
     * @ORM\Column(name="product_options", type="string", length=255, nullable=true)
     */
    protected $productOptions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_virtual", type="boolean")
     */
    protected $isVirtual;

    /**
     * @var float
     *
     * @ORM\Column(name="original_price", type="float", nullable=false)
     */
    protected $originalPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="float", nullable=true)
     */
    protected $discountPercent;

    /** Do not needed in magento order item, because magento api does not bring it up */
    protected $cost;

    /**
     * @param float $originalPrice
     *
     * @return $this
     */
    public function setOriginalPrice($originalPrice)
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    /**
     * @param string $productOptions
     *
     * @return $this
     */
    public function setProductOptions($productOptions)
    {
        $this->productOptions = $productOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductOptions()
    {
        return $this->productOptions;
    }

    /**
     * @param string $productType
     *
     * @return $this
     */
    public function setProductType($productType)
    {
        $this->productType = $productType;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param boolean $isVirtual
     *
     * @return $this
     */
    public function setIsVirtual($isVirtual)
    {
        $this->isVirtual = $isVirtual;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsVirtual()
    {
        return $this->isVirtual;
    }

    /**
     * @param float $discountPercent
     *
     * @return $this
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }
}
