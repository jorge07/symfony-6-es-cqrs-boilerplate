<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use Broadway\Serializer\Serializable;

final class UserSignedIn implements Serializable
{
    /** @var Email */
    public $email;

    public static function fromEmail(Email $email)
    {
        $instance = new self();

        $instance->email = $email;

        return $instance;
    }

    public static function deserialize(array $data)
    {
        return self::fromEmail(
            Email::fromString($data['email'])
        );
    }

    public function serialize(): array
    {
        return [
            'email' => $this->email->toString()
        ];
    }


}
