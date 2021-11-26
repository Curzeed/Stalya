<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Reponses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('reponses',CollectionType::class, [
                    // each entry in the array will be an "email" field
                    'entry_type' => ReponsesType::class,
                    // these options are passed to each "email" type
                    'entry_options' => [
                        'attr' => ['class' => 'email-box'],
                    ],
                    'allow_add' => true
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
