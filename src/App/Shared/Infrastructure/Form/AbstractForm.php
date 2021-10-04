<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Form;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractForm
{
    public const
        CREATE = 'POST',
        REPLACE = 'PUT',
        UPDATE = 'PATCH'
    ;

    protected function __construct(
        private FormFactoryInterface $formFactory,
        private string $formClass
    ) {}

    /**
     * Maybe Generics one day
     * @throws FormException
     */
    protected function execute(string $action = self::CREATE, array $data = [], $object = null, array $options = []): object
    {
        $form = $this->createForm($options, $action, $object)->submit($data, self::UPDATE !== $action);

        if (!$form->isSubmitted() || !$form->isValid()) {

            throw new FormException($form);
        }

        $object = $form->getData();

        $this->updatableCompliant($object, $action);

        return $object;
    }

    private function updatableCompliant(object $object, string $action): void
    {
        if (in_array($action, [self::UPDATE, self::REPLACE]) && method_exists($object, 'setUpdatedAt')) {
            $object->setUpdatedAt();
        }
    }

    public function createForm(array $options = [], string $action = self::CREATE, object|null $object = null): FormInterface
    {
        return $this->formFactory->create($this->formClass, $object, array_merge([
            'method' => $action,
        ], $options));
    }
}
