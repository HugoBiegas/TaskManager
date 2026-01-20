<?php

namespace App\Security;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter pour les autorisations sur les tâches
 * Exemple d'implémentation du pattern Voter de Symfony
 */
class TaskVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie si l'attribut est supporté et si le sujet est une tâche
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // L'utilisateur doit être connecté
        if (!$user instanceof User) {
            return false;
        }

        /** @var Task $task */
        $task = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($task, $user),
            self::EDIT => $this->canEdit($task, $user),
            self::DELETE => $this->canDelete($task, $user),
            default => false,
        };
    }

    private function canView(Task $task, User $user): bool
    {
        // Le propriétaire peut voir sa tâche
        if ($task->getOwner() === $user) {
            return true;
        }

        // Les admins peuvent tout voir
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }

    private function canEdit(Task $task, User $user): bool
    {
        // Seul le propriétaire peut modifier
        if ($task->getOwner() === $user) {
            return true;
        }

        // Les admins peuvent tout modifier
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }

    private function canDelete(Task $task, User $user): bool
    {
        // Même règles que pour l'édition
        return $this->canEdit($task, $user);
    }
}
