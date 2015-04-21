<?php

namespace CanalTP\NmmPortalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;

/**
 * Description of PerimeterType
 *
 * @author rabikhalil
 */
class PerimeterType extends AbstractType
{
    private $coverages = null;
    private $navitia = null;
    protected $withPerimeters = true;

    public function __construct($coverages, $navitia, $withPerimeters = true)
    {
        $this->navitia = $navitia;
        $this->coverages = array();
        $this->withPerimeters = $withPerimeters;

        $this->fetchCoverages($coverages);
    }

    private function fetchCoverages($coverages)
    {
        foreach ($coverages as $coverage) {
            $this->coverages[$coverage->id] = $coverage->id;
        }
        asort($this->coverages);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'external_coverage_id',
            'choice',
            array(
                'choices' => $this->coverages,
                'empty_value' => 'global.please_choose'
            )
        );

        if ($this->withPerimeters) {
            $builder->add(
                'external_network_id',
                'choice',
                array(
                    'choices' => array(),
                    'empty_value' => 'global.please_choose'
                )
            );
        }

        $formFactory = $builder->getFormFactory();
        $callback = function (FormEvent $event) use ($formFactory) {
            $data = $event->getData();
            $form = $event->getForm();
            if (!is_null($data) && $this->withPerimeters) {
                $externalCoverageId = (is_array($data) ? $data['external_coverage_id'] : $data->getExternalCoverageId());
                $externalNetworkId = (is_array($data) ? $data['external_network_id'] : $data->getExternalNetworkId());

                $form->remove('external_network_id');
                $networks = $this->navitia->getNetWorks($externalCoverageId);


                $form->add(
                    $formFactory->createNamed(
                        'external_network_id',
                        'choice',
                        $externalNetworkId,
                        array(
                            'auto_initialize' => false,
                            'choices' => $networks
                        )
                    )
                );
            }
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $callback);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callback);
    }

    public function getName()
    {
        return 'perimeter';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\NmmPortalBundle\Entity\Perimeter'
            )
        );
    }
}
