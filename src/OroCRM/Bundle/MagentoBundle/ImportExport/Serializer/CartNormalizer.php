<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Provider\StoreConnector;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter;

class CartNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ADDRESS_TYPE = 'OroCRM\Bundle\MagentoBundle\Entity\CartAddress';

    /** @var AddressDataConverter */
    protected $addressConverter;

    public function __construct(
        EntityManager $em,
        AddressDataConverter $addressConverter
    ) {
        parent::__construct($em);
        $this->addressConverter = $addressConverter;
    }


    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Cart;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == 'OroCRM\Bundle\MagentoBundle\Entity\Cart';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (method_exists($object, 'toArray')) {
            $result = $object->toArray($format, $context);
        } else {
            $result = array(
                'id'          => $object->getId(),
                'customer_id' => $object->getCustomer() ? $object->getCustomer()->getId() : null,
                'email'       => $object->getEmail(),
                'store'       => $object->getStore() ? $object->getStore()->getCode() : null,
                'origin_id'   => $object->getOriginId(),
                'items_qty'   => $object->getItemsQty(),
                'grand_total' => $object->getGrandTotal(),
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $channel    = $this->getChannelFromContext($context);
        $serializer = $this->serializer;

        $data['cartItems'] = $serializer->denormalize(
            $data['cartItems'],
            CartItemNormalizer::ENTITIES_TYPE,
            $format,
            $context
        );

        $data['customer'] = $this->denormalizeCustomer($data, $format, $context);

        $website = $serializer->denormalize($data['store']['website'], StoreConnector::WEBSITE_TYPE, $format, $context);
        $website->setChannel($channel);

        $data['store'] = $serializer->denormalize(
            $data['store'],
            StoreConnector::STORE_TYPE,
            $format,
            $context
        );
        $data['store']->setWebsite($website);
        $data['store']->setChannel($channel);

        $data = $this->denormalizeCreatedUpdated($data, $format, $context);

        $data['shippingAddress'] = $this->denormalizeAddress($data, 'shipping', $format, $context);
        $data['billingAddress']  = $this->denormalizeAddress($data, 'billing', $format, $context);

        $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);

        $cart = new Cart();
        $this->fillResultObject($cart, $data);

        $cart->setChannel($channel);

        return $cart;
    }

    /**
     * @param $data
     * @param $format
     * @param $context
     *
     * @return Customer
     */
    protected function denormalizeCustomer($data, $format, $context)
    {
        $group = $this->serializer->denormalize(
            $data['customer']['group'],
            CustomerDenormalizer::GROUPS_TYPE,
            $format,
            $context
        );
        $group->setChannel($this->getChannelFromContext($context));

        $customer = new Customer();
        $this->fillResultObject($customer, $data['customer']);

        if (!empty($data['email'])) {
            $customer->setEmail($data['email']);
        }

        return $customer;
    }

    /**
     * @param array  $data
     * @param string $type shipping or billing
     * @param string $format
     * @param array  $context
     *
     * @return CartAddress
     */
    protected function denormalizeAddress($data, $type, $format, $context)
    {
        $key  = $type . '_address';
        $data = $this->addressConverter->convertToImportFormat($data[$key]);

        if (empty($data['country'])) {
            return null;
        } else {
            return $this->serializer
                ->denormalize($data, self::ADDRESS_TYPE, $format, $context)
                ->setOriginId($data['originId']);
        }
    }
}
