<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    private Task $task;

    protected function setUp(): void
    {
        $this->task = new Task();
    }

    public function testNewTaskHasDefaultValues(): void
    {
        $this->assertSame(TaskStatus::TODO, $this->task->getStatus());
        $this->assertSame(TaskPriority::MEDIUM, $this->task->getPriority());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->task->getCreatedAt());
        $this->assertNull($this->task->getCompletedAt());
    }

    public function testSetTitle(): void
    {
        $this->task->setTitle('Test Task');
        $this->assertSame('Test Task', $this->task->getTitle());
    }

    public function testSetDescription(): void
    {
        $this->task->setDescription('Test Description');
        $this->assertSame('Test Description', $this->task->getDescription());
    }

    public function testSetStatusToDonesetsCompletedAt(): void
    {
        $this->task->setStatus(TaskStatus::DONE);

        $this->assertSame(TaskStatus::DONE, $this->task->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->task->getCompletedAt());
    }

    public function testSetStatusFromDoneToTodoClearsCompletedAt(): void
    {
        $this->task->setStatus(TaskStatus::DONE);
        $this->assertNotNull($this->task->getCompletedAt());

        $this->task->setStatus(TaskStatus::TODO);
        $this->assertNull($this->task->getCompletedAt());
    }

    public function testIsOverdueReturnsFalseWhenNoDueDate(): void
    {
        $this->assertFalse($this->task->isOverdue());
    }

    public function testIsOverdueReturnsFalseWhenCompleted(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('-1 day'));
        $this->task->setStatus(TaskStatus::DONE);

        $this->assertFalse($this->task->isOverdue());
    }

    public function testIsOverdueReturnsTrueWhenPastDue(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('-1 day'));
        $this->task->setStatus(TaskStatus::TODO);

        $this->assertTrue($this->task->isOverdue());
    }

    public function testIsOverdueReturnsFalseWhenFutureDue(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('+1 day'));

        $this->assertFalse($this->task->isOverdue());
    }

    public function testIsDueTodayReturnsTrueWhenDueToday(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('today'));

        $this->assertTrue($this->task->isDueToday());
    }

    public function testIsDueTodayReturnsFalseWhenNotDueToday(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('+1 day'));

        $this->assertFalse($this->task->isDueToday());
    }

    public function testIsDueSoonReturnsTrueWithinThreeDays(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('+2 days'));

        $this->assertTrue($this->task->isDueSoon());
    }

    public function testIsDueSoonReturnsFalseAfterThreeDays(): void
    {
        $this->task->setDueDate(new \DateTimeImmutable('+5 days'));

        $this->assertFalse($this->task->isDueSoon());
    }

    public function testIsCompletedReturnsTrueWhenStatusIsDone(): void
    {
        $this->task->setStatus(TaskStatus::DONE);

        $this->assertTrue($this->task->isCompleted());
    }

    public function testIsCompletedReturnsFalseWhenStatusIsNotDone(): void
    {
        $this->task->setStatus(TaskStatus::IN_PROGRESS);

        $this->assertFalse($this->task->isCompleted());
    }

    public function testSetOwner(): void
    {
        $user = new User();
        $this->task->setOwner($user);

        $this->assertSame($user, $this->task->getOwner());
    }

    public function testSetCategory(): void
    {
        $category = new Category();
        $this->task->setCategory($category);

        $this->assertSame($category, $this->task->getCategory());
    }

    public function testToStringReturnsTitle(): void
    {
        $this->task->setTitle('My Task');

        $this->assertSame('My Task', (string) $this->task);
    }

    public function testToStringReturnsEmptyWhenNoTitle(): void
    {
        $this->assertSame('', (string) $this->task);
    }
}
