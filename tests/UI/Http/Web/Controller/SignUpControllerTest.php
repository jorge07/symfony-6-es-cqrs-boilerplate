<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SignUpControllerTest extends WebTestCase
{
    /**
     * @test
     *
     * @group e2e
     */
    public function sign_up_page_form_format()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-up');

        $this->assertEquals(1, $crawler->filter('label:contains("Email")')->count());
        $this->assertEquals(1, $crawler->selectButton('Send')->count());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function sign_up_form_create_user_success()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/sign-up');

        $form = $crawler->selectButton('Send')->form();

        $form['email'] = $email = 'jorge.arcoma@gmail.com';

        $crawler = $client->submit($form);

        self::assertEquals(1, $crawler->filter('html:contains("Hello ' . $email .'")')->count());
        self::assertEquals(1, $crawler->filter('html:contains("Your id is ")')->count());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function sign_up_form_create_user_invalid_email()
    {
        $crawler = $this->createUser('jorge@gmail');

        self::assertEquals(1, $crawler->filter('html:contains("Not a valid email")')->count());
    }

    /**
     * @test
     *
     * @group e2e
     */
    public function sign_up_form_create_user_with_email_already_taken()
    {
        $this->sign_up_form_create_user_success();

        $crawler = $this->createUser('jorge.arcoma@gmail.com');
        
        self::assertEquals(1, $crawler->filter('html:contains("Email already exists.")')->count());
    }

    private function createUser(string $email): Crawler
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/sign-up');

        $form = $crawler->selectButton('Send')->form();

        $form->get('email')->setValue($email);

        $crawler = $client->submit($form);

        return $crawler;
    }
}
