<?php

namespace App\Form\Event;

use App\Entity\Category;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('startAt', null, [
                'widget' => 'single_text'
            ])
            ->add('endAt', null, [
                'widget' => 'single_text'
            ])
            ->add('capacity')
            ->add('status')
            ->add('slug', HiddenType::class, [
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (is_array($data) && isset($data['title']) && is_string($data['title']) && '' !== trim($data['title'])) {
                $data['slug'] = self::slugify($data['title']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }

    private static function slugify(string $value): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-')) . '-' . uniqid();
    }
}
