<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour TaskController
 */
class TaskControllerTest extends WebTestCase
{
    public function testIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        $this->assertResponseRedirects('/login');
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'TaskManager');
    }

    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewTaskRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks/new');

        $this->assertResponseRedirects('/login');
    }

    public function testSearchPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks/search');

        $this->assertResponseRedirects('/login');
    }

    public function testCategoryIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/categories');

        $this->assertResponseRedirects('/login');
    }
}
