<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testTaskListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        self::assertResponseRedirects('/login');
    }

    public function testNewTaskPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks/new');

        self::assertResponseRedirects('/login');
    }

    public function testTaskListPageLoadsForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Create a test user if not exists
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $client->request('GET', '/tasks');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mes tâches');
    }

    public function testNewTaskPageLoadsForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $client->request('GET', '/tasks/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouvelle tâche');
    }

    public function testNewTaskFormHasRequiredFields(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $crawler = $client->request('GET', '/tasks/new');

        self::assertSelectorExists('input[id$="_title"]');
        self::assertSelectorExists('textarea[id$="_description"]');
        self::assertSelectorExists('select[id$="_status"]');
        self::assertSelectorExists('select[id$="_priority"]');
    }
}
