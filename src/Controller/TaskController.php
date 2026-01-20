<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'app_task_index', methods: ['GET'])]
    public function index(#[CurrentUser] User $user, Request $request): Response
    {
        // Filtrer par statut si demandé
        $statusFilter = $request->query->get('status');

        if ($statusFilter && TaskStatus::tryFrom($statusFilter)) {
            $tasks = $this->taskRepository->findByOwnerAndStatus($user, TaskStatus::from($statusFilter));
        } else {
            $tasks = $this->taskRepository->findByOwnerWithCategory($user);
        }

        // Statistiques
        $stats = $this->taskRepository->countByStatus($user);
        $overdueTasks = $this->taskRepository->findOverdue($user);
        $todayTasks = $this->taskRepository->findDueToday($user);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'stats' => $stats,
            'overdueTasks' => $overdueTasks,
            'todayTasks' => $todayTasks,
            'currentStatus' => $statusFilter,
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser] User $user, Request $request): Response
    {
        $task = new Task();
        $task->setOwner($user);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été créée avec succès.');

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    #[IsGranted('view', subject: 'task')]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', subject: 'task')]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été modifiée avec succès.');

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    #[IsGranted('delete', subject: 'task')]
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été supprimée.');
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/status/{status}', name: 'app_task_change_status', methods: ['POST'])]
    #[IsGranted('edit', subject: 'task')]
    public function changeStatus(Request $request, Task $task, string $status): Response
    {
        if ($this->isCsrfTokenValid('status'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $newStatus = TaskStatus::tryFrom($status);

            if ($newStatus) {
                $task->setStatus($newStatus);
                $this->entityManager->flush();

                $this->addFlash('success', sprintf(
                    'La tâche "%s" est maintenant "%s".',
                    $task->getTitle(),
                    $newStatus->label()
                ));
            }
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search', name: 'app_task_search', methods: ['GET'], priority: 10)]
    public function search(#[CurrentUser] User $user, Request $request): Response
    {
        $query = $request->query->get('q', '');
        $tasks = [];

        if (strlen($query) >= 2) {
            $tasks = $this->taskRepository->search($user, $query);
        }

        return $this->render('task/search.html.twig', [
            'tasks' => $tasks,
            'query' => $query,
        ]);
    }
}
