<?php

namespace App\Tests\Unit\Enum;

use App\Enum\TaskPriority;
use PHPUnit\Framework\TestCase;

class TaskPriorityTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $cases = TaskPriority::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(TaskPriority::LOW, $cases);
        $this->assertContains(TaskPriority::MEDIUM, $cases);
        $this->assertContains(TaskPriority::HIGH, $cases);
        $this->assertContains(TaskPriority::URGENT, $cases);
    }

    public function testValues(): void
    {
        $this->assertSame('low', TaskPriority::LOW->value);
        $this->assertSame('medium', TaskPriority::MEDIUM->value);
        $this->assertSame('high', TaskPriority::HIGH->value);
        $this->assertSame('urgent', TaskPriority::URGENT->value);
    }

    public function testLabels(): void
    {
        $this->assertSame('Basse', TaskPriority::LOW->label());
        $this->assertSame('Moyenne', TaskPriority::MEDIUM->label());
        $this->assertSame('Haute', TaskPriority::HIGH->label());
        $this->assertSame('Urgente', TaskPriority::URGENT->label());
    }

    public function testColors(): void
    {
        $this->assertSame('gray', TaskPriority::LOW->color());
        $this->assertSame('blue', TaskPriority::MEDIUM->color());
        $this->assertSame('orange', TaskPriority::HIGH->color());
        $this->assertSame('red', TaskPriority::URGENT->color());
    }

    public function testSortOrder(): void
    {
        $this->assertSame(1, TaskPriority::LOW->sortOrder());
        $this->assertSame(2, TaskPriority::MEDIUM->sortOrder());
        $this->assertSame(3, TaskPriority::HIGH->sortOrder());
        $this->assertSame(4, TaskPriority::URGENT->sortOrder());
    }

    public function testSortOrderIsInAscendingOrder(): void
    {
        $this->assertLessThan(TaskPriority::MEDIUM->sortOrder(), TaskPriority::LOW->sortOrder());
        $this->assertLessThan(TaskPriority::HIGH->sortOrder(), TaskPriority::MEDIUM->sortOrder());
        $this->assertLessThan(TaskPriority::URGENT->sortOrder(), TaskPriority::HIGH->sortOrder());
    }
}
