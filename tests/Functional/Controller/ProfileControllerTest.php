<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testProfileSettingsRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile/settings');

        self::assertResponseRedirects('/login');
    }

    public function testProfilePasswordRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile/password');

        self::assertResponseRedirects('/login');
    }

    public function testProfileSettingsPageLoadsForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $client->request('GET', '/profile/settings');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Paramètres du profil');
    }

    public function testProfilePasswordPageLoadsForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $client->request('GET', '/profile/password');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Changer le mot de passe');
    }

    public function testProfileSettingsFormHasRequiredFields(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $crawler = $client->request('GET', '/profile/settings');

        self::assertSelectorExists('input[id$="_firstName"]');
        self::assertSelectorExists('input[id$="_lastName"]');
        self::assertSelectorExists('input[id$="_email"]');
    }

    public function testProfilePasswordFormHasRequiredFields(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($user === null) {
            $this->markTestSkipped('No test user available. Run fixtures first.');
        }

        $client->loginUser($user);
        $crawler = $client->request('GET', '/profile/password');

        self::assertSelectorExists('input[id$="_currentPassword"]');
        self::assertSelectorExists('input[id$="_newPassword_first"]');
        self::assertSelectorExists('input[id$="_newPassword_second"]');
    }
}
