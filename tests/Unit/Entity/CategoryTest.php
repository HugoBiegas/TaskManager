<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    public function testNewCategoryHasDefaultValues(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getCreatedAt());
        $this->assertSame('#6366f1', $this->category->getColor());
        $this->assertCount(0, $this->category->getTasks());
    }

    public function testSetName(): void
    {
        $this->category->setName('Work');
        $this->assertSame('Work', $this->category->getName());
    }

    public function testSetColor(): void
    {
        $this->category->setColor('#ff0000');
        $this->assertSame('#ff0000', $this->category->getColor());
    }

    public function testSetDescription(): void
    {
        $this->category->setDescription('Work related tasks');
        $this->assertSame('Work related tasks', $this->category->getDescription());
    }

    public function testSetOwner(): void
    {
        $user = new User();
        $this->category->setOwner($user);

        $this->assertSame($user, $this->category->getOwner());
    }

    public function testAddTask(): void
    {
        $task = new Task();
        $this->category->addTask($task);

        $this->assertCount(1, $this->category->getTasks());
        $this->assertTrue($this->category->getTasks()->contains($task));
        $this->assertSame($this->category, $task->getCategory());
    }

    public function testAddTaskDoesNotDuplicate(): void
    {
        $task = new Task();
        $this->category->addTask($task);
        $this->category->addTask($task);

        $this->assertCount(1, $this->category->getTasks());
    }

    public function testRemoveTask(): void
    {
        $task = new Task();
        $this->category->addTask($task);
        $this->category->removeTask($task);

        $this->assertCount(0, $this->category->getTasks());
        $this->assertNull($task->getCategory());
    }

    public function testGetTaskCount(): void
    {
        $task1 = new Task();
        $task2 = new Task();

        $this->category->addTask($task1);
        $this->category->addTask($task2);

        $this->assertSame(2, $this->category->getTaskCount());
    }

    public function testToStringReturnsName(): void
    {
        $this->category->setName('Personal');

        $this->assertSame('Personal', (string) $this->category);
    }

    public function testToStringReturnsEmptyWhenNoName(): void
    {
        $this->assertSame('', (string) $this->category);
    }
}
