<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\TaskPriority;
use App\Entity\TaskStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Crée le QueryBuilder de base pour les tâches d'un utilisateur
     */
    public function createQueryBuilderForUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $user)
            ->orderBy('t.createdAt', 'DESC');
    }

    /**
     * Trouve toutes les tâches d'un utilisateur avec les relations chargées
     *
     * @return Task[]
     */
    public function findByOwnerWithCategory(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.category', 'c')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $user)
            ->orderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches d'un utilisateur par statut
     *
     * @return Task[]
     */
    public function findByOwnerAndStatus(User $user, TaskStatus $status): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.category', 'c')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status = :status')
            ->setParameter('owner', $user)
            ->setParameter('status', $status)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches en retard d'un utilisateur
     *
     * @return Task[]
     */
    public function findOverdue(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.dueDate < :today')
            ->andWhere('t.status NOT IN (:excludedStatuses)')
            ->setParameter('owner', $user)
            ->setParameter('today', new \DateTime('today'))
            ->setParameter('excludedStatuses', [TaskStatus::DONE, TaskStatus::CANCELLED])
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches dues aujourd'hui
     *
     * @return Task[]
     */
    public function findDueToday(User $user): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.dueDate >= :today')
            ->andWhere('t.dueDate < :tomorrow')
            ->andWhere('t.status NOT IN (:excludedStatuses)')
            ->setParameter('owner', $user)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('excludedStatuses', [TaskStatus::DONE, TaskStatus::CANCELLED])
            ->orderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches par catégorie
     *
     * @return Task[]
     */
    public function findByCategory(User $user, Category $category): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.category = :category')
            ->setParameter('owner', $user)
            ->setParameter('category', $category)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les tâches par titre ou description
     *
     * @return Task[]
     */
    public function search(User $user, string $query): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.category', 'c')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.title LIKE :query OR t.description LIKE :query')
            ->setParameter('owner', $user)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les tâches par statut pour un utilisateur
     *
     * @return array<string, int>
     */
    public function countByStatus(User $user): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('t.status', 'COUNT(t.id) as count')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $user)
            ->groupBy('t.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach (TaskStatus::cases() as $status) {
            $counts[$status->value] = 0;
        }

        foreach ($result as $row) {
            $counts[$row['status']->value] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Trouve les tâches urgentes non complétées
     *
     * @return Task[]
     */
    public function findUrgent(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.priority = :priority')
            ->andWhere('t.status NOT IN (:excludedStatuses)')
            ->setParameter('owner', $user)
            ->setParameter('priority', TaskPriority::URGENT)
            ->setParameter('excludedStatuses', [TaskStatus::DONE, TaskStatus::CANCELLED])
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des tâches complétées par période
     *
     * @return array<string, int>
     */
    public function getCompletionStats(User $user, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('DATE(t.completedAt) as date', 'COUNT(t.id) as count')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status = :status')
            ->andWhere('t.completedAt >= :from')
            ->andWhere('t.completedAt <= :to')
            ->setParameter('owner', $user)
            ->setParameter('status', TaskStatus::DONE)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['date']] = (int) $row['count'];
        }

        return $stats;
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
