<?php

namespace App\Tests\Integration\Security;

use App\Entity\Task;
use App\Entity\User;
use App\Security\Voter\TaskVoter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class TaskVoterTest extends KernelTestCase
{
    private ?AccessDecisionManagerInterface $accessDecisionManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->accessDecisionManager = static::getContainer()->get(AccessDecisionManagerInterface::class);
    }

    private function createUser(int $id): User
    {
        $user = new User();
        $user->setEmail("user{$id}@test.com");
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword('password');

        // Use reflection to set the id
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, $id);

        return $user;
    }

    private function createTask(User $owner): Task
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setOwner($owner);

        return $task;
    }

    private function createToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'main', $user->getRoles());
    }

    public function testOwnerCanViewTask(): void
    {
        $owner = $this->createUser(1);
        $task = $this->createTask($owner);
        $token = $this->createToken($owner);

        $this->assertTrue(
            $this->accessDecisionManager->decide($token, [TaskVoter::VIEW], $task)
        );
    }

    public function testOwnerCanEditTask(): void
    {
        $owner = $this->createUser(2);
        $task = $this->createTask($owner);
        $token = $this->createToken($owner);

        $this->assertTrue(
            $this->accessDecisionManager->decide($token, [TaskVoter::EDIT], $task)
        );
    }

    public function testOwnerCanDeleteTask(): void
    {
        $owner = $this->createUser(3);
        $task = $this->createTask($owner);
        $token = $this->createToken($owner);

        $this->assertTrue(
            $this->accessDecisionManager->decide($token, [TaskVoter::DELETE], $task)
        );
    }

    public function testNonOwnerCannotViewTask(): void
    {
        $owner = $this->createUser(4);
        $otherUser = $this->createUser(5);
        $task = $this->createTask($owner);
        $token = $this->createToken($otherUser);

        $this->assertFalse(
            $this->accessDecisionManager->decide($token, [TaskVoter::VIEW], $task)
        );
    }

    public function testNonOwnerCannotEditTask(): void
    {
        $owner = $this->createUser(6);
        $otherUser = $this->createUser(7);
        $task = $this->createTask($owner);
        $token = $this->createToken($otherUser);

        $this->assertFalse(
            $this->accessDecisionManager->decide($token, [TaskVoter::EDIT], $task)
        );
    }

    public function testNonOwnerCannotDeleteTask(): void
    {
        $owner = $this->createUser(8);
        $otherUser = $this->createUser(9);
        $task = $this->createTask($owner);
        $token = $this->createToken($otherUser);

        $this->assertFalse(
            $this->accessDecisionManager->decide($token, [TaskVoter::DELETE], $task)
        );
    }
}
