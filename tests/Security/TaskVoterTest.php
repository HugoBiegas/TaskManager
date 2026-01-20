<?php

namespace App\Tests\Security;

use App\Entity\Task;
use App\Entity\User;
use App\Security\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Tests unitaires pour le TaskVoter
 */
class TaskVoterTest extends TestCase
{
    private TaskVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new TaskVoter();
    }

    public function testOwnerCanViewTask(): void
    {
        $user = $this->createUser(1);
        $task = $this->createTaskForUser($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $task, [TaskVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testOwnerCanEditTask(): void
    {
        $user = $this->createUser(1);
        $task = $this->createTaskForUser($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $task, [TaskVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testOwnerCanDeleteTask(): void
    {
        $user = $this->createUser(1);
        $task = $this->createTaskForUser($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $task, [TaskVoter::DELETE]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testNonOwnerCannotViewTask(): void
    {
        $owner = $this->createUser(1);
        $otherUser = $this->createUser(2);
        $task = $this->createTaskForUser($owner);
        $token = $this->createToken($otherUser);

        $result = $this->voter->vote($token, $task, [TaskVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testNonOwnerCannotEditTask(): void
    {
        $owner = $this->createUser(1);
        $otherUser = $this->createUser(2);
        $task = $this->createTaskForUser($owner);
        $token = $this->createToken($otherUser);

        $result = $this->voter->vote($token, $task, [TaskVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAdminCanViewAnyTask(): void
    {
        $owner = $this->createUser(1);
        $admin = $this->createUser(2, ['ROLE_ADMIN']);
        $task = $this->createTaskForUser($owner);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $task, [TaskVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAdminCanEditAnyTask(): void
    {
        $owner = $this->createUser(1);
        $admin = $this->createUser(2, ['ROLE_ADMIN']);
        $task = $this->createTaskForUser($owner);
        $token = $this->createToken($admin);

        $result = $this->voter->vote($token, $task, [TaskVoter::EDIT]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testVoterAbstainsForNonTaskSubject(): void
    {
        $user = $this->createUser(1);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, new \stdClass(), [TaskVoter::VIEW]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testVoterAbstainsForUnsupportedAttribute(): void
    {
        $user = $this->createUser(1);
        $task = $this->createTaskForUser($user);
        $token = $this->createToken($user);

        $result = $this->voter->vote($token, $task, ['UNSUPPORTED']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    private function createUser(int $id, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setEmail("user{$id}@example.com");
        $user->setFirstName('Test');
        $user->setLastName("User{$id}");
        $user->setPassword('password');
        $user->setRoles($roles);

        // Simuler l'ID via reflection
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, $id);

        return $user;
    }

    private function createTaskForUser(User $user): Task
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setOwner($user);

        return $task;
    }

    private function createToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'main', $user->getRoles());
    }
}
