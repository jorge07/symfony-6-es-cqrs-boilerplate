<?php

declare(strict_types=1);

namespace App\Task\Infrastructure;

use App\Task\Domain\Task;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaskSymfonyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uuid', HiddenType::class, [
                'mapped' => false,
                'data' => UuidV4::uuid4()->toString(),
            ])
            ->add('userId', HiddenType::class, [
                'mapped' => false,
                'data' => $options['userId']
            ])
            ->add('title', TextType::class)
            ->add('completedAt', CheckboxType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true, // allow this if needed
            'userId' => null,
            'empty_data' => static function (FormInterface $form) {
                return new Task(
                    $form->get('uuid')->getData(),
                    $form->get('userId')->getData(),
                    $form->get('title')->getData(),
                    $form->get('completedAt')->getData(),
                );
            }
        ]);
    }
}
