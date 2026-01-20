<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * API REST pour les tâches
 * Exemple de controller API natif Symfony
 */
#[Route('/api/v1/tasks', name: 'api_task_')]
class TaskApiController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Liste toutes les tâches de l'utilisateur connecté
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $statusFilter = $request->query->get('status');

        if ($statusFilter && TaskStatus::tryFrom($statusFilter)) {
            $tasks = $this->taskRepository->findByOwnerAndStatus($user, TaskStatus::from($statusFilter));
        } else {
            $tasks = $this->taskRepository->findByOwnerWithCategory($user);
        }

        return $this->json($tasks, context: ['groups' => ['task:read']]);
    }

    /**
     * Récupère une tâche par son ID
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('view', subject: 'task')]
    public function show(Task $task): JsonResponse
    {
        return $this->json($task, context: ['groups' => ['task:read', 'task:detail']]);
    }

    /**
     * Crée une nouvelle tâche
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task = new Task();
        $task->setOwner($user);
        $task->setTitle($data['title'] ?? '');
        $task->setDescription($data['description'] ?? null);

        if (isset($data['priority'])) {
            $task->setPriority(\App\Entity\TaskPriority::tryFrom($data['priority']) ?? \App\Entity\TaskPriority::MEDIUM);
        }

        if (isset($data['dueDate'])) {
            $task->setDueDate(new \DateTime($data['dueDate']));
        }

        // Validation
        $errors = $this->validator->validate($task);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json(
            $task,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_task_show', ['id' => $task->getId()])],
            ['groups' => ['task:read']]
        );
    }

    /**
     * Met à jour une tâche existante
     */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('edit', subject: 'task')]
    public function update(Request $request, Task $task): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }

        if (isset($data['status'])) {
            $status = TaskStatus::tryFrom($data['status']);
            if ($status) {
                $task->setStatus($status);
            }
        }

        if (isset($data['priority'])) {
            $priority = \App\Entity\TaskPriority::tryFrom($data['priority']);
            if ($priority) {
                $task->setPriority($priority);
            }
        }

        if (array_key_exists('dueDate', $data)) {
            $task->setDueDate($data['dueDate'] ? new \DateTime($data['dueDate']) : null);
        }

        // Validation
        $errors = $this->validator->validate($task);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($task, context: ['groups' => ['task:read']]);
    }

    /**
     * Supprime une tâche
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('delete', subject: 'task')]
    public function delete(Task $task): JsonResponse
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Change le statut d'une tâche
     */
    #[Route('/{id}/status', name: 'change_status', methods: ['PATCH'])]
    #[IsGranted('edit', subject: 'task')]
    public function changeStatus(Request $request, Task $task): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return $this->json(['error' => 'Le statut est requis'], Response::HTTP_BAD_REQUEST);
        }

        $status = TaskStatus::tryFrom($data['status']);

        if (!$status) {
            return $this->json([
                'error' => 'Statut invalide',
                'valid_statuses' => array_map(fn($s) => $s->value, TaskStatus::cases()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $task->setStatus($status);
        $this->entityManager->flush();

        return $this->json($task, context: ['groups' => ['task:read']]);
    }

    /**
     * Statistiques des tâches de l'utilisateur
     */
    #[Route('/stats', name: 'stats', methods: ['GET'], priority: 10)]
    public function stats(#[CurrentUser] User $user): JsonResponse
    {
        $stats = $this->taskRepository->countByStatus($user);
        $overdue = count($this->taskRepository->findOverdue($user));
        $dueToday = count($this->taskRepository->findDueToday($user));

        return $this->json([
            'byStatus' => $stats,
            'overdue' => $overdue,
            'dueToday' => $dueToday,
            'total' => array_sum($stats),
        ]);
    }
}
