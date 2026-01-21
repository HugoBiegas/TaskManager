<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Form\TaskType;
use App\Repository\CategoryRepository;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tasks')]
final class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'app_task_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $statusFilter = $request->query->get('status');
        $categoryFilter = $request->query->get('category');
        $search = $request->query->get('search');

        $status = $statusFilter ? TaskStatus::tryFrom($statusFilter) : null;
        $categoryId = $categoryFilter ? (int) $categoryFilter : null;

        $tasks = $this->taskRepository->findByOwnerWithFilters($user, $status, $categoryId, $search);
        $categories = $this->categoryRepository->findByOwner($user);
        $stats = $this->taskRepository->getStatsByOwner($user);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'categories' => $categories,
            'stats' => $stats,
            'currentStatus' => $statusFilter,
            'currentCategory' => $categoryFilter,
            'search' => $search,
            'statuses' => TaskStatus::cases(),
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $task = new Task();
        $task->setOwner($user);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'Tâche créée avec succès.');

            return $this->redirectToRoute('app_task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    #[IsGranted(TaskVoter::VIEW, subject: 'task')]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted(TaskVoter::EDIT, subject: 'task')]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Tâche modifiée avec succès.');

            return $this->redirectToRoute('app_task_index');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    #[IsGranted(TaskVoter::DELETE, subject: 'task')]
    public function delete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'Tâche supprimée avec succès.');
        }

        return $this->redirectToRoute('app_task_index');
    }

    #[Route('/{id}/toggle-status', name: 'app_task_toggle_status', methods: ['POST'])]
    #[IsGranted(TaskVoter::EDIT, subject: 'task')]
    public function toggleStatus(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('toggle' . $task->getId(), $request->getPayload()->getString('_token'))) {
            $newStatus = match ($task->getStatus()) {
                TaskStatus::TODO => TaskStatus::IN_PROGRESS,
                TaskStatus::IN_PROGRESS => TaskStatus::DONE,
                TaskStatus::DONE => TaskStatus::TODO,
                TaskStatus::CANCELLED => TaskStatus::TODO,
            };

            $task->setStatus($newStatus);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Statut changé en "%s".', $newStatus->label()));
        }

        return $this->redirectToRoute('app_task_index');
    }
}
