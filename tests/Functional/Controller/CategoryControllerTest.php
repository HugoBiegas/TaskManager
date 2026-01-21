<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testCategoryListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/categories');

        self::assertResponseRedirects('/login');
    }

    public function testNewCategoryPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/categories/new');

        self::assertResponseRedirects('/login');
    }

    public function testCategoryListPageLoadsForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $client->request('GET', '/categories');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mes catégories');
    }

    public function testNewCategoryPageLoadsForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $client->request('GET', '/categories/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouvelle catégorie');
    }

    public function testNewCategoryFormHasRequiredFields(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $crawler = $client->request('GET', '/categories/new');

        self::assertSelectorExists('input[id$="_name"]');
        self::assertSelectorExists('input[id$="_color"]');
        self::assertSelectorExists('textarea[id$="_description"]');
    }
}
