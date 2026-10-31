<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\ContactInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['autocomplete' => 'name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 7],
            ])
            ->add('website', TextType::class, [
                'label' => 'Site web',
                'required' => false,
                'row_attr' => ['class' => 'honeypot', 'aria-hidden' => 'true'],
                'attr' => ['tabindex' => -1, 'autocomplete' => 'off'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactInput::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
