<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class OpportunityType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'closeReason',
            'entity',
            array(
                'label' => 'orocrm.sales.opportunity.close_reason.label',
                'class' => 'OroCRMSalesBundle:OpportunityCloseReason',
                'property' => 'label',
                'required' => false,
                'disabled' => false,
            )
        );

        $builder
            ->add(
                'contact',
                'orocrm_contact_select',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.contact.label')
            )
            ->add(
                'account',
                'orocrm_account_select',
                array('required' => true, 'label' => 'orocrm.sales.opportunity.account.label')
            )
            ->add('name', 'text', array('required' => true, 'label' => 'orocrm.sales.opportunity.name.label'))
            ->add(
                'closeDate',
                'oro_date',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.close_date.label')
            )
            ->add(
                'probability',
                'percent',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.probability.label')
            )
            ->add(
                'budgetAmount',
                'oro_money',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.budget_amount.label')
            )
            ->add(
                'closeRevenue',
                'oro_money',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.close_revenue.label')
            )
            ->add(
                'customerNeed',
                'text',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.customer_need.label')
            )
            ->add(
                'proposedSolution',
                'text',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.proposed_solution.label')
            )
            ->add(
                'notes',
                'textarea',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.notes.label')
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'intention'  => 'opportunity',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
