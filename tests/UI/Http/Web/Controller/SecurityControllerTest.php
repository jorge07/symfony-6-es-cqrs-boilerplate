<?php

declare(strict_types=1);

namespace Tests\App\UI\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function sign_in_after_create_user(): void
    {
        $client = $this->createUser('jorge@gmail.com');

        $crawler = $client->request('GET', '/sign-in');

        $form = $crawler->selectButton('Sign in')->form();

        $form->get('_email')->setValue('jorge@gmail.com');
        $form->get('_password')->setValue('crqs-demo');

        $client->submit($form);

        $crawler = $client->followRedirect();

        self::assertSame('/profile', \parse_url($crawler->getUri(), \PHP_URL_PATH));
        self::assertSame(1, $crawler->filter('html:contains("Hi jorge@gmail.com")')->count());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function logout_should_remove_session_and_profile_redirect_sign_in(): void
    {
        $client = $this->createUser('jorge@gmail.com');

        $crawler = $client->request('GET', '/sign-in');

        $form = $crawler->selectButton('Sign in')->form();

        $form->get('_email')->setValue('jorge@gmail.com');
        $form->get('_password')->setValue('crqs-demo');

        $client->submit($form);

        $crawler = $client->followRedirect();
        self::assertSame('/profile', \parse_url($crawler->getUri(), \PHP_URL_PATH));

        $client->click($crawler->selectLink('Exit')->link());

        $crawler = $client->followRedirect();
        self::assertSame('/', \parse_url($crawler->getUri(), \PHP_URL_PATH));

        $client->request('GET', '/profile');

        $crawler = $client->followRedirect();
        self::assertSame('/sign-in', \parse_url($crawler->getUri(), \PHP_URL_PATH));
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function login_should_display_an_error_when_bad_credentials(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-in');

        $form = $crawler->selectButton('Sign in')->form();

        $form->get('_email')->setValue('an@email.com');
        $form->get('_password')->setValue('password-so-safe');

        $client->submit($form);

        $crawler = $client->followRedirect();
        self::assertSame(1, $crawler->filter('html:contains("An authentication exception occurred.")')->count());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function login_should_display_an_error_when_bad_invalid_email(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-in');

        $form = $crawler->selectButton('Sign in')->form();

        $form->get('_email')->setValue('an@email');
        $form->get('_password')->setValue('password-so-safe');

        $client->submit($form);

        $crawler = $client->followRedirect();
        self::assertSame(1, $crawler->filter('html:contains("An authentication exception occurred.")')->count());
    }

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
