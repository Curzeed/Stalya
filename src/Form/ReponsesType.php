<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Reponses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponsesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label',null,['label'=>'Réponse :'])
            ->add('is_correct')
            ->add('question',EntityType::class,[
                'label' => 'Question : ',
                'class'=> Question::class,
                'choice_label' =>'label',
                'mapped'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponses::class,
        ]);
    }
}
