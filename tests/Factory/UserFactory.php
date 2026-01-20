<?php

namespace App\Tests\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->email(),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'password' => 'password123', // Sera hashé automatiquement en test
            'roles' => ['ROLE_USER'],
            'isVerified' => true,
        ];
    }

    /**
     * Crée un utilisateur admin
     */
    public function asAdmin(): self
    {
        return $this->with(['roles' => ['ROLE_ADMIN']]);
    }

    /**
     * Crée un utilisateur non vérifié
     */
    public function unverified(): self
    {
        return $this->with(['isVerified' => false]);
    }

    /**
     * Avec un email spécifique
     */
    public function withEmail(string $email): self
    {
        return $this->with(['email' => $email]);
    }
}
