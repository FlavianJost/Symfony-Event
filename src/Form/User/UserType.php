<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,[
                'label' => 'Username',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username'
                    ]), new Length(min: 3, max: 100)
                ]
            ])
            ->add('email', EmailType::class,[
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email'
                    ]), new Email([
                        'message' => 'Please enter a valid email'
                    ])
                ]
            ])
            ->add('plainPassword', PasswordType::class,[
                'label' => 'Password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password'
                    ]), new Length(min: 12, max: 255)
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
