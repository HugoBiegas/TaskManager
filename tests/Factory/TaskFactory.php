<?php

namespace App\Tests\Factory;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\TaskPriority;
use App\Entity\TaskStatus;
use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Task>
 */
final class TaskFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Task::class;
    }

    protected function defaults(): array
    {
        return [
            'title' => self::faker()->sentence(4),
            'description' => self::faker()->optional()->paragraph(),
            'status' => self::faker()->randomElement(TaskStatus::cases()),
            'priority' => self::faker()->randomElement(TaskPriority::cases()),
            'dueDate' => self::faker()->optional()->dateTimeBetween('now', '+30 days'),
            'owner' => UserFactory::new(),
        ];
    }

    /**
     * Tâche avec un statut spécifique
     */
    public function withStatus(TaskStatus $status): self
    {
        return $this->with(['status' => $status]);
    }

    /**
     * Tâche à faire
     */
    public function todo(): self
    {
        return $this->withStatus(TaskStatus::TODO);
    }

    /**
     * Tâche en cours
     */
    public function inProgress(): self
    {
        return $this->withStatus(TaskStatus::IN_PROGRESS);
    }

    /**
     * Tâche terminée
     */
    public function done(): self
    {
        return $this->withStatus(TaskStatus::DONE);
    }

    /**
     * Tâche avec une priorité spécifique
     */
    public function withPriority(TaskPriority $priority): self
    {
        return $this->with(['priority' => $priority]);
    }

    /**
     * Tâche urgente
     */
    public function urgent(): self
    {
        return $this->withPriority(TaskPriority::URGENT);
    }

    /**
     * Tâche en retard
     */
    public function overdue(): self
    {
        return $this->with([
            'dueDate' => self::faker()->dateTimeBetween('-30 days', '-1 day'),
            'status' => TaskStatus::TODO,
        ]);
    }

    /**
     * Tâche due aujourd'hui
     */
    public function dueToday(): self
    {
        return $this->with([
            'dueDate' => new \DateTime('today'),
            'status' => TaskStatus::TODO,
        ]);
    }

    /**
     * Tâche pour un utilisateur spécifique
     */
    public function forUser(User $user): self
    {
        return $this->with(['owner' => $user]);
    }

    /**
     * Tâche avec une catégorie
     */
    public function withCategory(Category $category): self
    {
        return $this->with(['category' => $category]);
    }
}
