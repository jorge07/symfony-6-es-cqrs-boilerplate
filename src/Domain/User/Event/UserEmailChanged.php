<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use Broadway\Serializer\Serializable;

class UserEmailChanged implements Serializable
{
    /**
     * @var Email
     */
    public $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self(Email::fromString($data['email']));
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'email' => $this->email->toString()
        ];
    }
}
