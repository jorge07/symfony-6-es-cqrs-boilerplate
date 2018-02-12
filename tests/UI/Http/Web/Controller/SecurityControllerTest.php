<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function sign_in_after_create_user()
    {
        $this->createUser('jorge@gmail.com');

        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-in');

        $form = $crawler->selectButton('Sign in')->form();

        $form->get('_email')->setValue('jorge@gmail.com');
        $form->get('_password')->setValue('crqs-demo');
        
        $client->submit($form);

        $crawler = $client->followRedirect();

        self::assertEquals('/profile', parse_url($crawler->getUri(), PHP_URL_PATH));
        self::assertEquals(1, $crawler->filter('html:contains("Hi jorge@gmail.com")')->count());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function logout_should_remove_session_and_profile_redirect_sign_in()
    {
        $this->createUser('jorge@gmail.com');

        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-in');

        $form = $crawler->selectButton('Sign in')->form();

        $form->get('_email')->setValue('jorge@gmail.com');
        $form->get('_password')->setValue('crqs-demo');

        $client->submit($form);

        $crawler = $client->followRedirect();
        self::assertEquals('/profile', parse_url($crawler->getUri(), PHP_URL_PATH));

        $client->click($crawler->selectLink('Exit')->link());

        $crawler = $client->followRedirect();
        self::assertEquals('/', parse_url($crawler->getUri(), PHP_URL_PATH));

        $client->request('GET', '/profile');

        $crawler = $client->followRedirect();
        self::assertEquals('/sign-in', parse_url($crawler->getUri(), PHP_URL_PATH));
    }

    private function createUser(string $email, string $password = 'crqs-demo'): Crawler
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-up');

        $form = $crawler->selectButton('Send')->form();

        $form->get('email')->setValue($email);
        $form->get('password')->setValue($password);

        $crawler = $client->submit($form);

        return $crawler;
    }
}
