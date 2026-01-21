<?php

namespace App\Tests\Unit\Enum;

use App\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $cases = TaskStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(TaskStatus::TODO, $cases);
        $this->assertContains(TaskStatus::IN_PROGRESS, $cases);
        $this->assertContains(TaskStatus::DONE, $cases);
        $this->assertContains(TaskStatus::CANCELLED, $cases);
    }

    public function testValues(): void
    {
        $this->assertSame('todo', TaskStatus::TODO->value);
        $this->assertSame('in_progress', TaskStatus::IN_PROGRESS->value);
        $this->assertSame('done', TaskStatus::DONE->value);
        $this->assertSame('cancelled', TaskStatus::CANCELLED->value);
    }

    public function testLabels(): void
    {
        $this->assertSame('À faire', TaskStatus::TODO->label());
        $this->assertSame('En cours', TaskStatus::IN_PROGRESS->label());
        $this->assertSame('Terminée', TaskStatus::DONE->label());
        $this->assertSame('Annulée', TaskStatus::CANCELLED->label());
    }

    public function testColors(): void
    {
        $this->assertSame('gray', TaskStatus::TODO->color());
        $this->assertSame('blue', TaskStatus::IN_PROGRESS->color());
        $this->assertSame('green', TaskStatus::DONE->color());
        $this->assertSame('red', TaskStatus::CANCELLED->color());
    }

    public function testIcons(): void
    {
        $this->assertSame('circle', TaskStatus::TODO->icon());
        $this->assertSame('clock', TaskStatus::IN_PROGRESS->icon());
        $this->assertSame('check-circle', TaskStatus::DONE->icon());
        $this->assertSame('x-circle', TaskStatus::CANCELLED->icon());
    }

    public function testTryFromValidValue(): void
    {
        $this->assertSame(TaskStatus::TODO, TaskStatus::tryFrom('todo'));
        $this->assertSame(TaskStatus::DONE, TaskStatus::tryFrom('done'));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(TaskStatus::tryFrom('invalid'));
    }
}
