<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\TaskPriority;
use App\Entity\TaskStatus;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Task
 */
class TaskTest extends TestCase
{
    public function testTaskCreation(): void
    {
        $task = new Task();
        $task->setTitle('Ma tâche de test');

        $this->assertSame('Ma tâche de test', $task->getTitle());
        $this->assertSame(TaskStatus::TODO, $task->getStatus());
        $this->assertSame(TaskPriority::MEDIUM, $task->getPriority());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
    }

    public function testSetStatusToDoneRecordsCompletedAt(): void
    {
        $task = new Task();
        $task->setTitle('Test task');

        $this->assertNull($task->getCompletedAt());

        $task->setStatus(TaskStatus::DONE);

        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCompletedAt());
    }

    public function testSetStatusFromDoneResetsCompletedAt(): void
    {
        $task = new Task();
        $task->setTitle('Test task');
        $task->setStatus(TaskStatus::DONE);

        $this->assertNotNull($task->getCompletedAt());

        $task->setStatus(TaskStatus::IN_PROGRESS);

        $this->assertNull($task->getCompletedAt());
    }

    public function testIsOverdueReturnsTrueForPastDueDate(): void
    {
        $task = new Task();
        $task->setTitle('Overdue task');
        $task->setDueDate(new \DateTime('-1 day'));
        $task->setStatus(TaskStatus::TODO);

        $this->assertTrue($task->isOverdue());
    }

    public function testIsOverdueReturnsFalseForFutureDueDate(): void
    {
        $task = new Task();
        $task->setTitle('Future task');
        $task->setDueDate(new \DateTime('+7 days'));
        $task->setStatus(TaskStatus::TODO);

        $this->assertFalse($task->isOverdue());
    }

    public function testIsOverdueReturnsFalseForCompletedTask(): void
    {
        $task = new Task();
        $task->setTitle('Completed overdue task');
        $task->setDueDate(new \DateTime('-1 day'));
        $task->setStatus(TaskStatus::DONE);

        $this->assertFalse($task->isOverdue());
    }

    public function testIsOverdueReturnsFalseWhenNoDueDate(): void
    {
        $task = new Task();
        $task->setTitle('No due date task');
        $task->setStatus(TaskStatus::TODO);

        $this->assertFalse($task->isOverdue());
    }

    public function testIsDueTodayReturnsTrue(): void
    {
        $task = new Task();
        $task->setTitle('Today task');
        $task->setDueDate(new \DateTime('today'));

        $this->assertTrue($task->isDueToday());
    }

    public function testIsDueTodayReturnsFalseForOtherDates(): void
    {
        $task = new Task();
        $task->setTitle('Tomorrow task');
        $task->setDueDate(new \DateTime('tomorrow'));

        $this->assertFalse($task->isDueToday());
    }

    public function testTaskOwnerRelation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('Test');
        $user->setLastName('User');

        $task = new Task();
        $task->setTitle('User task');
        $task->setOwner($user);

        $this->assertSame($user, $task->getOwner());
    }

    /**
     * @dataProvider priorityLabelProvider
     */
    public function testPriorityLabels(TaskPriority $priority, string $expectedLabel): void
    {
        $this->assertSame($expectedLabel, $priority->label());
    }

    public static function priorityLabelProvider(): array
    {
        return [
            [TaskPriority::LOW, 'Basse'],
            [TaskPriority::MEDIUM, 'Moyenne'],
            [TaskPriority::HIGH, 'Haute'],
            [TaskPriority::URGENT, 'Urgente'],
        ];
    }

    /**
     * @dataProvider statusLabelProvider
     */
    public function testStatusLabels(TaskStatus $status, string $expectedLabel): void
    {
        $this->assertSame($expectedLabel, $status->label());
    }

    public static function statusLabelProvider(): array
    {
        return [
            [TaskStatus::TODO, 'À faire'],
            [TaskStatus::IN_PROGRESS, 'En cours'],
            [TaskStatus::DONE, 'Terminée'],
            [TaskStatus::CANCELLED, 'Annulée'],
        ];
    }
}
