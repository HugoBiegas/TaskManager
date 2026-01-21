<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?TaskRepository $taskRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->taskRepository = $this->entityManager->getRepository(Task::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function createTestUser(string $email = 'repotest@example.com'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword('hashed_password');

        return $user;
    }

    private function createTestTask(User $owner, string $title, TaskStatus $status = TaskStatus::TODO): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setOwner($owner);
        $task->setStatus($status);

        return $task;
    }

    public function testFindByOwnerReturnsOnlyOwnersTasks(): void
    {
        $user1 = $this->createTestUser('user1-repo@test.com');
        $user2 = $this->createTestUser('user2-repo@test.com');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);

        $task1 = $this->createTestTask($user1, 'User 1 Task');
        $task2 = $this->createTestTask($user2, 'User 2 Task');

        $this->entityManager->persist($task1);
        $this->entityManager->persist($task2);
        $this->entityManager->flush();

        $user1Tasks = $this->taskRepository->findByOwner($user1);
        $user2Tasks = $this->taskRepository->findByOwner($user2);

        $this->assertCount(1, $user1Tasks);
        $this->assertCount(1, $user2Tasks);
        $this->assertSame('User 1 Task', $user1Tasks[0]->getTitle());
        $this->assertSame('User 2 Task', $user2Tasks[0]->getTitle());

        // Cleanup
        $this->entityManager->remove($task1);
        $this->entityManager->remove($task2);
        $this->entityManager->remove($user1);
        $this->entityManager->remove($user2);
        $this->entityManager->flush();
    }

    public function testFindByOwnerAndStatusFiltersCorrectly(): void
    {
        $user = $this->createTestUser('status-test@test.com');
        $this->entityManager->persist($user);

        $todoTask = $this->createTestTask($user, 'Todo Task', TaskStatus::TODO);
        $doneTask = $this->createTestTask($user, 'Done Task', TaskStatus::DONE);

        $this->entityManager->persist($todoTask);
        $this->entityManager->persist($doneTask);
        $this->entityManager->flush();

        $todoTasks = $this->taskRepository->findByOwnerAndStatus($user, TaskStatus::TODO);
        $doneTasks = $this->taskRepository->findByOwnerAndStatus($user, TaskStatus::DONE);

        $this->assertCount(1, $todoTasks);
        $this->assertCount(1, $doneTasks);
        $this->assertSame('Todo Task', $todoTasks[0]->getTitle());
        $this->assertSame('Done Task', $doneTasks[0]->getTitle());

        // Cleanup
        $this->entityManager->remove($todoTask);
        $this->entityManager->remove($doneTask);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function testGetStatsByOwnerReturnsCorrectCounts(): void
    {
        $user = $this->createTestUser('stats-test@test.com');
        $this->entityManager->persist($user);

        $tasks = [
            $this->createTestTask($user, 'Task 1', TaskStatus::TODO),
            $this->createTestTask($user, 'Task 2', TaskStatus::TODO),
            $this->createTestTask($user, 'Task 3', TaskStatus::IN_PROGRESS),
            $this->createTestTask($user, 'Task 4', TaskStatus::DONE),
        ];

        foreach ($tasks as $task) {
            $this->entityManager->persist($task);
        }
        $this->entityManager->flush();

        $stats = $this->taskRepository->getStatsByOwner($user);

        $this->assertSame(4, $stats['total']);
        $this->assertSame(2, $stats['todo']);
        $this->assertSame(1, $stats['in_progress']);
        $this->assertSame(1, $stats['done']);

        // Cleanup
        foreach ($tasks as $task) {
            $this->entityManager->remove($task);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
