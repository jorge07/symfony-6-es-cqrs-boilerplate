<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Auth;

use App\Domain\User\ValueObject\Email;

class Credentials
{
    /**
     * @var Email
     */
    private $email;

    /**
     * @var HashedPassword
     */
    private $password;

    public function __construct(Email $email, HashedPassword $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
    
    public function email()
    {
        return $this->email;
    }
    
    public function password()
    {
        return $this->password;
    }
}
