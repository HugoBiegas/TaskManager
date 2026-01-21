<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create test user
        $testUser = new User();
        $testUser->setEmail('test@example.com');
        $testUser->setFirstName('Test');
        $testUser->setLastName('User');
        $testUser->setPassword($this->passwordHasher->hashPassword($testUser, 'password123'));
        $manager->persist($testUser);

        // Create admin user
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('User');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));
        $manager->persist($adminUser);

        // Create categories for test user
        $workCategory = new Category();
        $workCategory->setName('Travail');
        $workCategory->setColor('#3B82F6');
        $workCategory->setDescription('Tâches professionnelles');
        $workCategory->setOwner($testUser);
        $manager->persist($workCategory);

        $personalCategory = new Category();
        $personalCategory->setName('Personnel');
        $personalCategory->setColor('#10B981');
        $personalCategory->setDescription('Tâches personnelles');
        $personalCategory->setOwner($testUser);
        $manager->persist($personalCategory);

        $urgentCategory = new Category();
        $urgentCategory->setName('Urgent');
        $urgentCategory->setColor('#EF4444');
        $urgentCategory->setDescription('Tâches urgentes');
        $urgentCategory->setOwner($testUser);
        $manager->persist($urgentCategory);

        // Create tasks for test user
        $task1 = new Task();
        $task1->setTitle('Terminer le rapport mensuel');
        $task1->setDescription('Compléter le rapport de ventes du mois de janvier');
        $task1->setStatus(TaskStatus::IN_PROGRESS);
        $task1->setPriority(TaskPriority::HIGH);
        $task1->setDueDate(new \DateTimeImmutable('+2 days'));
        $task1->setCategory($workCategory);
        $task1->setOwner($testUser);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Réunion équipe');
        $task2->setDescription('Préparer la présentation pour la réunion hebdomadaire');
        $task2->setStatus(TaskStatus::TODO);
        $task2->setPriority(TaskPriority::MEDIUM);
        $task2->setDueDate(new \DateTimeImmutable('+1 week'));
        $task2->setCategory($workCategory);
        $task2->setOwner($testUser);
        $manager->persist($task2);

        $task3 = new Task();
        $task3->setTitle('Faire les courses');
        $task3->setDescription('Liste: lait, pain, fruits, légumes');
        $task3->setStatus(TaskStatus::TODO);
        $task3->setPriority(TaskPriority::LOW);
        $task3->setDueDate(new \DateTimeImmutable('+3 days'));
        $task3->setCategory($personalCategory);
        $task3->setOwner($testUser);
        $manager->persist($task3);

        $task4 = new Task();
        $task4->setTitle('Appeler le médecin');
        $task4->setDescription('Prendre rendez-vous pour le bilan annuel');
        $task4->setStatus(TaskStatus::TODO);
        $task4->setPriority(TaskPriority::URGENT);
        $task4->setDueDate(new \DateTimeImmutable('today'));
        $task4->setCategory($urgentCategory);
        $task4->setOwner($testUser);
        $manager->persist($task4);

        $task5 = new Task();
        $task5->setTitle('Réviser le code du projet');
        $task5->setDescription('Faire une revue de code du nouveau module');
        $task5->setStatus(TaskStatus::DONE);
        $task5->setPriority(TaskPriority::MEDIUM);
        $task5->setDueDate(new \DateTimeImmutable('-1 day'));
        $task5->setCategory($workCategory);
        $task5->setOwner($testUser);
        $manager->persist($task5);

        $manager->flush();
    }
}
