<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Form;

use InvalidArgumentException;
use Symfony\Component\Form\FormInterface;

final class FormException extends InvalidArgumentException
{
    public function __construct(public FormInterface $form)
    {
        parent::__construct($form->getErrors()->__toString());
    }
}
