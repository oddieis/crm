<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

class CartStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRM\Bundle\MagentoBundle\Entity\Cart';

    /** @var StoreStrategy */
    protected $storeStrategy;

    public function __construct(ImportStrategyHelper $strategyHelper, StoreStrategy $storeStrategy)
    {
        parent::__construct($strategyHelper);
        $this->storeStrategy = $storeStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function process($newEntity)
    {
        $existingEntity = $this->getEntityByCriteria(
            ['originId' => $newEntity->getOriginId(), 'channel' => $newEntity->getChannel()],
            $newEntity
        );

        if ($existingEntity) {
            $this->strategyHelper->importEntity(
                $existingEntity,
                $newEntity,
                ['id', 'store', 'status', 'cartItems', 'customer', 'shippingAddress', 'billingAddress']
            );
        } else {
            $existingEntity = $newEntity;
        }

        if (!$existingEntity->getStore() || !$existingEntity->getStore()->getId()) {
            $existingEntity->setStore(
                $this->storeStrategy->process($newEntity->getStore())
            );
        }

        $newEntity->getCustomer()->setChannel($newEntity->getChannel());
        $this->updateCustomer($existingEntity, $newEntity->getCustomer())
            ->updateAddresses($existingEntity, $newEntity)
            ->updateCartItems($existingEntity, $newEntity->getCartItems());

        $this->validateAndUpdateContext($existingEntity);

        return $existingEntity;
    }

    /**
     * Assign existing customer, if not found - set null
     *
     * @param Cart     $newCart
     * @param Customer $customer
     *
     * @return CartStrategy
     */
    protected function updateCustomer(Cart $newCart, Customer $customer)
    {
        $existingCustomer = $this->getEntityByCriteria(
            ['originId' => $customer->getOriginId(), 'channel' => $customer->getChannel()],
            $customer
        );

        if ($existingCustomer) {
            $newCart->setCustomer($existingCustomer);
        } else {
            $newCart->setCustomer(null);
        }

        return $this;
    }

    /**
     * @param Cart            $cart
     * @param ArrayCollection $cartItems imported items
     *
     * @return CartStrategy
     */
    protected function updateCartItems(Cart $cart, ArrayCollection $cartItems)
    {
        $importedOriginIds = $cartItems->map(
            function ($item) {
                return $item->getOriginId();
            }
        )->toArray();

        // insert new and update existing items
        /** $item - imported cart item */
        foreach ($cartItems as $item) {
            $originId = $item->getOriginId();

            $existingItem = $cart->getCartItems()->filter(
                function ($item) use ($originId) {
                    return $item->getOriginId() == $originId;
                }
            )->first();

            if ($existingItem) {
                $this->strategyHelper->importEntity($existingItem, $item, ['id', 'cart']);
                $item = $existingItem;
            }

            if (!$item->getCart()) {
                $item->setCart($cart);
            }

            if (!$cart->getCartItems()->contains($item)) {
                $cart->getCartItems()->add($item);
            }
        }

        // delete cart items that not exists in remote cart
        $deletedCartItems = $cart->getCartItems()->filter(
            function ($item) use ($importedOriginIds) {
                return !in_array($item->getOriginId(), $importedOriginIds);
            }
        );
        foreach ($deletedCartItems as $item) {
            $cart->getCartItems()->remove($item);
        }

        return $this;
    }

    /**
     * @param Cart $newCart
     * @param Cart $importedCart
     *
     * @return CartStrategy
     */
    protected function updateAddresses(Cart $newCart, Cart $importedCart)
    {
        $addresses = ['ShippingAddress', 'BillingAddress'];

        foreach ($addresses as $addressName) {
            $addressGetter = 'get'.$addressName;
            $setter = 'set'.$addressName;
            $address = $importedCart->$addressGetter();

            if (!$address) {
                continue;
            }

            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;
            $originAddressId = $address->getOriginId();

            $existingAddress = $newCart->$addressGetter();
            if ($existingAddress && $existingAddress->getOriginId() == $originAddressId) {
                $this->strategyHelper->importEntity($existingAddress, $address, ['id', 'region', 'country']);
                $address = $existingAddress;
            }

            $this->updateAddressCountryRegion($address, $mageRegionId);
            $newCart->$setter($address);
        }

        return $this;
    }
}