<?php

namespace CanalTP\NmmPortalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Email;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use CanalTP\NmmPortalBundle\Form\DataTransformer\ApplicationToCustomerApplicationTransformer;
use CanalTP\NmmPortalBundle\Form\DataTransformer\ApplicationToCustomerApplicationTransformerWithToken;
use CanalTP\NmmPortalBundle\Entity\CustomerApplication;

/**
 * Description of CustomerType
 *
 * @author kevin
 */
class CustomerType extends \CanalTP\SamCoreBundle\Form\Type\CustomerType
{
    private $em = null;
    private $coverages = null;
    private $navitia = null;
    private $applicationsTransformer = null;
    private $applicationsTransformerWithToken = null;
    private $withTyr = false;

    public function __construct(
        EntityManager $em,
        $coverages,
        $navitia,
        ApplicationToCustomerApplicationTransformer $applicationsTransformer,
        ApplicationToCustomerApplicationTransformerWithToken $applicationsTransformerWithToken,
        $withTyr
    )
    {
        $this->em = $em;
        $this->coverages = $coverages;
        $this->navitia = $navitia;
        $this->applicationsTransformer = $applicationsTransformer;
        $this->applicationsTransformerWithToken = $applicationsTransformerWithToken;
        $this->withTyr = $withTyr;
    }

    private function addApplicationsField(FormBuilderInterface $builder)
    {
        if ($this->withTyr)
        {
            $builder->add(
                'applications',
                'entity',
                array(
                    'label' => 'customer.applications',
                    'multiple' => true,
                    'class' => 'CanalTPSamCoreBundle:Application',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('cli')
                            ->orderBy('cli.name', 'ASC');
                    },
                    'expanded' => true
                )
            )->addModelTransformer($this->applicationsTransformer);
        } else {
            $builder->add(
                'applications',
                'collection',
                array(
                    'label' => 'customer.applications',
                    'type' => new CustomerApplicationType()
                )
            )->addModelTransformer($this->applicationsTransformerWithToken);
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        //it's now in navitiaEntity
        $builder->remove('email');
        $builder->remove('name');

        $builder->add(
            'navitiaEntity',
            new NavitiaEntityType($this->coverages, $this->navitia),
            array(
                'label' => 'customer.navitia'
            )
        );

        $copyName = function (FormEvent $event) {
            $customer = $event->getData();
            $customer->setName($customer->getNavitiaEntity()->getName());
        };

        $builder->addEventListener(FormEvents::POST_SUBMIT, $copyName);

        $this->addApplicationsField($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\NmmPortalBundle\Entity\Customer',
                'invalid_message' => 'Tyr error: Email is duplicated'
            )
        );
    }
}
