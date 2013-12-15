<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\Order;

/**
 * @Route("/order")
 */
class OrderController extends Controller
{
    /**
     * @Route("/{id}", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_order_view")
     * @Template
     */
    public function indexAction($id)
    {
        return ['channelId' => $id];
    }

    /**
     * @Route("/view/{id}", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_order_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:Order"
     * )
     * @Template
     */
    public function viewAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route("/info/{id}", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_order_view")
     * @Template
     */
    public function infoAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Route("/widget/grid/{id}", requirements={"id"="\d+"}))
     * @AclAncestor("orocrm_magento_order_view")
     * @Template
     */
    public function itemsAction(Order $order)
    {
        return ['entity' => $order];
    }
}
