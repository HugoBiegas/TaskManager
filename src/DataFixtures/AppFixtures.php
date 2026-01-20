<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\TaskPriority;
use App\Entity\TaskStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer les catégories
        $categories = $this->createCategories($manager);

        // Créer un utilisateur de démo
        $user = $this->createDemoUser($manager);

        // Créer un admin
        $admin = $this->createAdminUser($manager);

        // Créer des tâches de démo
        $this->createDemoTasks($manager, $user, $categories);

        $manager->flush();
    }

    private function createCategories(ObjectManager $manager): array
    {
        $categoriesData = [
            ['name' => 'Travail', 'color' => '#3b82f6', 'description' => 'Tâches professionnelles'],
            ['name' => 'Personnel', 'color' => '#10b981', 'description' => 'Tâches personnelles'],
            ['name' => 'Urgent', 'color' => '#ef4444', 'description' => 'Tâches urgentes à traiter rapidement'],
            ['name' => 'Shopping', 'color' => '#f59e0b', 'description' => 'Liste de courses et achats'],
            ['name' => 'Santé', 'color' => '#ec4899', 'description' => 'Rendez-vous médicaux et sport'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setColor($data['color']);
            $category->setDescription($data['description']);
            $manager->persist($category);
            $categories[$data['name']] = $category;
        }

        return $categories;
    }

    private function createDemoUser(ObjectManager $manager): User
    {
        $user = new User();
        $user->setEmail('demo@taskmanager.com');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'demo1234'));

        $manager->persist($user);

        return $user;
    }

    private function createAdminUser(ObjectManager $manager): User
    {
        $admin = new User();
        $admin->setEmail('admin@taskmanager.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('Système');
        $admin->setIsVerified(true);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin1234'));

        $manager->persist($admin);

        return $admin;
    }

    private function createDemoTasks(ObjectManager $manager, User $user, array $categories): void
    {
        $tasksData = [
            [
                'title' => 'Finaliser le rapport mensuel',
                'description' => 'Compléter le rapport de janvier avec les statistiques de ventes.',
                'status' => TaskStatus::IN_PROGRESS,
                'priority' => TaskPriority::HIGH,
                'category' => 'Travail',
                'dueDate' => new \DateTime('+2 days'),
            ],
            [
                'title' => 'Réunion d\'équipe',
                'description' => 'Préparer la présentation pour la réunion hebdomadaire.',
                'status' => TaskStatus::TODO,
                'priority' => TaskPriority::MEDIUM,
                'category' => 'Travail',
                'dueDate' => new \DateTime('+1 day'),
            ],
            [
                'title' => 'Appeler le médecin',
                'description' => 'Prendre rendez-vous pour le check-up annuel.',
                'status' => TaskStatus::TODO,
                'priority' => TaskPriority::LOW,
                'category' => 'Santé',
                'dueDate' => new \DateTime('+7 days'),
            ],
            [
                'title' => 'Acheter des fournitures',
                'description' => 'Stylos, papier, classeurs pour le bureau.',
                'status' => TaskStatus::TODO,
                'priority' => TaskPriority::LOW,
                'category' => 'Shopping',
                'dueDate' => null,
            ],
            [
                'title' => 'Corriger le bug critique',
                'description' => 'Le formulaire de connexion ne fonctionne pas sur mobile.',
                'status' => TaskStatus::TODO,
                'priority' => TaskPriority::URGENT,
                'category' => 'Urgent',
                'dueDate' => new \DateTime('today'),
            ],
            [
                'title' => 'Formation Symfony',
                'description' => 'Suivre le module 3 de la formation en ligne.',
                'status' => TaskStatus::DONE,
                'priority' => TaskPriority::MEDIUM,
                'category' => 'Personnel',
                'dueDate' => new \DateTime('-2 days'),
            ],
            [
                'title' => 'Revue de code',
                'description' => 'Revoir les PR en attente sur le projet principal.',
                'status' => TaskStatus::TODO,
                'priority' => TaskPriority::MEDIUM,
                'category' => 'Travail',
                'dueDate' => new \DateTime('+3 days'),
            ],
            [
                'title' => 'Mettre à jour les dépendances',
                'description' => 'Effectuer les mises à jour de sécurité sur tous les projets.',
                'status' => TaskStatus::TODO,
                'priority' => TaskPriority::HIGH,
                'category' => 'Travail',
                'dueDate' => new \DateTime('-1 day'), // En retard !
            ],
        ];

        foreach ($tasksData as $data) {
            $task = new Task();
            $task->setTitle($data['title']);
            $task->setDescription($data['description']);
            $task->setStatus($data['status']);
            $task->setPriority($data['priority']);
            $task->setOwner($user);
            $task->setDueDate($data['dueDate']);

            if (isset($categories[$data['category']])) {
                $task->setCategory($categories[$data['category']]);
            }

            $manager->persist($task);
        }
    }
}
