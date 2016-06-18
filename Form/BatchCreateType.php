<?php

namespace BiteCodes\RestApiGeneratorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (Kernel::MAJOR_VERSION == 2 && Kernel::MINOR_VERSION == 7) {
            $dynamicFormType = 'dynamic_form_type';
            $collectionType = 'collection';
            $entryType = 'type';
            $entryOptionsKey = 'options';
        } else {
            $dynamicFormType = DynamicFormType::class;
            $collectionType = CollectionType::class;
            $entryType = 'entry_type';
            $entryOptionsKey = 'entry_options';
        }

        $entryOptions = [
            'data_class' => get_class($options['object'])
        ];

        if ($options['type'] == $dynamicFormType) {
            $entryOptions['object'] = $options['object'];
        }

        $builder
            ->add('entities', $collectionType, [
                $entryType => $options['type'],
                $entryOptionsKey => $entryOptions,
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
        return 'batch_create_type';
    }
}