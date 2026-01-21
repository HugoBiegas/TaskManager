<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testNewUserHasDefaultValues(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
        $this->assertTrue($this->user->isActive());
        $this->assertCount(0, $this->user->getTasks());
    }

    public function testSetEmail(): void
    {
        $this->user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $this->user->getEmail());
    }

    public function testSetFirstName(): void
    {
        $this->user->setFirstName('John');
        $this->assertSame('John', $this->user->getFirstName());
    }

    public function testSetLastName(): void
    {
        $this->user->setLastName('Doe');
        $this->assertSame('Doe', $this->user->getLastName());
    }

    public function testGetFullName(): void
    {
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');

        $this->assertSame('John Doe', $this->user->getFullName());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $this->user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $this->user->getUserIdentifier());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $roles = $this->user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetRoles(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);

        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testIsAdminReturnsTrueWhenHasAdminRole(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);

        $this->assertTrue($this->user->isAdmin());
    }

    public function testIsAdminReturnsFalseWhenNoAdminRole(): void
    {
        $this->user->setRoles([]);

        $this->assertFalse($this->user->isAdmin());
    }

    public function testSetPassword(): void
    {
        $this->user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $this->user->getPassword());
    }

    public function testSetIsActive(): void
    {
        $this->user->setIsActive(false);
        $this->assertFalse($this->user->isActive());

        $this->user->setIsActive(true);
        $this->assertTrue($this->user->isActive());
    }

    public function testAddTask(): void
    {
        $task = new Task();
        $this->user->addTask($task);

        $this->assertCount(1, $this->user->getTasks());
        $this->assertTrue($this->user->getTasks()->contains($task));
        $this->assertSame($this->user, $task->getOwner());
    }

    public function testAddTaskDoesNotDuplicate(): void
    {
        $task = new Task();
        $this->user->addTask($task);
        $this->user->addTask($task);

        $this->assertCount(1, $this->user->getTasks());
    }

    public function testRemoveTask(): void
    {
        $task = new Task();
        $this->user->addTask($task);
        $this->user->removeTask($task);

        $this->assertCount(0, $this->user->getTasks());
        $this->assertNull($task->getOwner());
    }

    public function testSetAvatarFile(): void
    {
        $this->user->setAvatarFile(null);
        $this->assertNull($this->user->getAvatarFile());
    }

    public function testSetAvatarName(): void
    {
        $this->user->setAvatarName('avatar.jpg');
        $this->assertSame('avatar.jpg', $this->user->getAvatarName());
    }

    public function testSetAvatarSize(): void
    {
        $this->user->setAvatarSize(1024);
        $this->assertSame(1024, $this->user->getAvatarSize());
    }

    public function testSerializeAndUnserialize(): void
    {
        $this->user->setEmail('test@example.com');
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->user->setIsActive(true);

        $serialized = $this->user->__serialize();

        $newUser = new User();
        $newUser->__unserialize($serialized);

        $this->assertSame('test@example.com', $newUser->getEmail());
        $this->assertSame('John', $newUser->getFirstName());
        $this->assertSame('Doe', $newUser->getLastName());
        $this->assertTrue($newUser->isActive());
    }
}
