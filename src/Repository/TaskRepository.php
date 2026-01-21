<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @return Task[]
     */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByOwnerAndStatus(User $owner, TaskStatus $status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status = :status')
            ->setParameter('owner', $owner)
            ->setParameter('status', $status)
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findPendingByOwner(User $owner): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status IN (:statuses)')
            ->setParameter('owner', $owner)
            ->setParameter('statuses', [TaskStatus::TODO, TaskStatus::IN_PROGRESS])
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findOverdueByOwner(User $owner): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status IN (:statuses)')
            ->andWhere('t.dueDate < :today')
            ->setParameter('owner', $owner)
            ->setParameter('statuses', [TaskStatus::TODO, TaskStatus::IN_PROGRESS])
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findDueTodayByOwner(User $owner): array
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('t')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status IN (:statuses)')
            ->andWhere('t.dueDate = :today')
            ->setParameter('owner', $owner)
            ->setParameter('statuses', [TaskStatus::TODO, TaskStatus::IN_PROGRESS])
            ->setParameter('today', $today)
            ->orderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByOwnerWithFilters(
        User $owner,
        ?TaskStatus $status = null,
        ?int $categoryId = null,
        ?string $search = null
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner);

        if ($status !== null) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if ($categoryId !== null) {
            $qb->andWhere('t.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($search !== null && $search !== '') {
            $qb->andWhere('(t.title LIKE :search OR t.description LIKE :search)')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, int>
     */
    public function getStatsByOwner(User $owner): array
    {
        $results = $this->createQueryBuilder('t')
            ->select('t.status, COUNT(t.id) as count')
            ->andWhere('t.owner = :owner')
            ->setParameter('owner', $owner)
            ->groupBy('t.status')
            ->getQuery()
            ->getResult();

        $stats = [
            'total' => 0,
            'todo' => 0,
            'in_progress' => 0,
            'done' => 0,
            'cancelled' => 0,
            'overdue' => 0,
        ];

        foreach ($results as $result) {
            $status = $result['status']->value;
            $count = (int) $result['count'];
            $stats[$status] = $count;
            $stats['total'] += $count;
        }

        // Count overdue tasks
        $overdueCount = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.owner = :owner')
            ->andWhere('t.status IN (:statuses)')
            ->andWhere('t.dueDate < :today')
            ->setParameter('owner', $owner)
            ->setParameter('statuses', [TaskStatus::TODO, TaskStatus::IN_PROGRESS])
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getSingleScalarResult();

        $stats['overdue'] = (int) $overdueCount;

        return $stats;
    }
}
