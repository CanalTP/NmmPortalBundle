<?php

namespace CanalTP\NmmPortalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
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
        $this->coverages = array_merge(['' => 'global.please_choose'], $this->coverages);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'external_coverage_id',
            'choice',
            [
                'choices' => $this->coverages
            ]
        );

        if ($this->withPerimeters) {
            $builder->add(
                'external_network_id',
                'choice',
                [
                    'choices' => ['' => 'global.please_choose']
                ]
            );
        }

        $formFactory = $builder->getFormFactory();
        $callback = function (FormEvent $event) use ($formFactory) {
            $data = $event->getData();
            $form = $event->getForm();
            if (!is_null($data) && $this->withPerimeters) {
                $externalCoverageId = (is_array($data) ?
                    $data['external_coverage_id'] : $data->getExternalCoverageId());
                $externalNetworkId = (is_array($data) ?
                    $data['external_network_id'] : $data->getExternalNetworkId());
                // Enable edit customer even with a non existent coverage
                if (in_array($externalCoverageId, $this->coverages)) {
                    $form->remove('external_network_id');
                    $networks = $this->navitia->getNetWorks($externalCoverageId);
                    asort($networks);
                    $networks = array_merge(['' => 'global.please_choose'], $networks);
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
                    if (!array_key_exists($externalNetworkId, $networks)) {
                        // Add message on old network selected
                        $form->get('external_network_id')->addError(
                            new FormError('customer.network.undefined', null, ['%network%' => $externalNetworkId])
                        );
                    }
                } else {
                    // Add message on old coverage selected
                    $form->get('external_coverage_id')->addError(
                        new FormError('customer.coverage.undefined', null, ['%coverage%' => $externalCoverageId])
                    );
                }
            }
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, $callback);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callback);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CanalTP\NmmPortalBundle\Entity\Perimeter'
            )
        );
    }
}
