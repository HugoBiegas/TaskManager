<?php

namespace App\Tests\Factory;

use App\Entity\Category;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory
{
    private const COLORS = ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

    public static function class(): string
    {
        return Category::class;
    }

    protected function defaults(): array
    {
        return [
            'name' => self::faker()->unique()->word(),
            'color' => self::faker()->randomElement(self::COLORS),
            'description' => self::faker()->optional()->sentence(),
        ];
    }

    /**
     * Crée une catégorie avec un nom spécifique
     */
    public function withName(string $name): self
    {
        return $this->with(['name' => $name]);
    }

    /**
     * Crée des catégories par défaut communes
     */
    public static function createDefaults(): void
    {
        self::createOne(['name' => 'Travail', 'color' => '#3b82f6']);
        self::createOne(['name' => 'Personnel', 'color' => '#10b981']);
        self::createOne(['name' => 'Urgent', 'color' => '#ef4444']);
    }
}
