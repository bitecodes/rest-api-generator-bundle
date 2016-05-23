<?php

namespace BiteCodes\RestApiGeneratorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entities', CollectionType::class, [
                'entry_type' => $options['type'],
                'entry_options' => [
                    'object' => $options['object'],
                    'data_class' => get_class($options['object'])
                ],
                'allow_add' => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => null,
            'object' => null
        ]);
    }

    public function getName()
    {
        return 'batch_create';
    }
}