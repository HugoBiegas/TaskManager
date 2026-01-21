<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Connexion à votre compte');
    }

    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Créer un compte');
    }

    public function testLoginPageHasForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
        self::assertSelectorExists('input[name="_remember_me"]');
    }

    public function testRegisterPageHasForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        self::assertSelectorExists('form');
        self::assertSelectorExists('input[id$="_firstName"]');
        self::assertSelectorExists('input[id$="_lastName"]');
        self::assertSelectorExists('input[id$="_email"]');
    }

    public function testUnauthenticatedUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        self::assertResponseRedirects('/login');
    }

    public function testLoginLinkOnRegisterPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $link = $crawler->selectLink('connectez-vous à votre compte existant')->link();
        $client->click($link);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_login');
    }

    public function testRegisterLinkOnLoginPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $link = $crawler->selectLink('créez un nouveau compte')->link();
        $client->click($link);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_register');
    }
}
