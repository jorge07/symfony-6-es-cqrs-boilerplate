<?php

declare(strict_types=1);

namespace Tests\App\UI\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait CreateUserTrait
{
    private function createUser(string $email, string $password = 'crqs-demo'): KernelBrowser
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-up');

        $form = $crawler->selectButton('Send')->form();

        $form->get('email')->setValue($email);
        $form->get('password')->setValue($password);

        $client->submit($form);

        return $client;
    }
}
