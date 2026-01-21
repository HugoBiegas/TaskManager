<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testAdminPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users');

        self::assertResponseRedirects('/login');
    }

    public function testRegularUserCannotAccessAdmin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Find a non-admin user
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        // Make sure user is not admin
        if ($user->isAdmin()) {
            $this->markTestSkipped('Test user is already admin.');
        }

        $client->loginUser($user);
        $client->request('GET', '/admin/users');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminUserCanAccessAdminPage(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Find an admin user
        $users = $userRepository->findAll();
        $adminUser = null;

        foreach ($users as $user) {
            if ($user->isAdmin()) {
                $adminUser = $user;
                break;
            }
        }

        if ($adminUser === null) {
            $this->markTestSkipped('No admin user available. Create an admin user first.');
        }

        $client->loginUser($adminUser);
        $client->request('GET', '/admin/users');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Gestion des utilisateurs');
    }
}
