<?php

namespace Elazar\Dibby\User;

use DateInterval;
use DateTimeImmutable;
use Elazar\Dibby\{
    Exception,
    User\PasswordHasher,
    User\ResetTokenGenerator,
    User\UserRepository,
};

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private ResetTokenGenerator $resetTokenGenerator,
        private DateTimeImmutable $now,
        private string $resetTokenTimeToLive,
    ) { }

    public function persistUser(User $user): User
    {
        if (!filter_var($user->getEmail(), \FILTER_VALIDATE_EMAIL)) {
            throw Exception::invalidInput('E-mail is invalid');
        }
        if (empty($user->getPassword())) {
            throw Exception::invalidInput('Password is required');
        }
        $passwordHash = $this->passwordHasher->getHash($user->getPassword());
        $user = $user->withPasswordHash($passwordHash);
        return $this->userRepository->persistUser($user);
    }

    public function authenticateUser(string $email, string $password): ?User
    {
        try {
            $user = $this->userRepository->getUserByEmail($email);
        } catch (Exception $e) {
            return null;
        }

        $passwordHash = $user->getPasswordHash();
        if ($passwordHash === null) {
            return null;
        }

        return $this->passwordHasher->verifyHash($password, $passwordHash)
            ? $user : null;
    }

    public function startPasswordReset(string $email): ?User
    {
        try {
            $user = $this->userRepository->getUserByEmail($email);
        } catch (Exception $e) {
            return null;
        }

        $resetToken = $this->resetTokenGenerator->generateToken();

        $expirationInterval = new DateInterval($this->resetTokenTimeToLive);
        $resetTokenExpiration = $this->now->add($expirationInterval);

        $user = $user
            ->withResetToken($resetToken)
            ->withResetTokenExpiration($resetTokenExpiration);

        return $this->userRepository->persistUser($user);
    }

    public function verifyPasswordReset(string $userId, string $resetToken): ?User
    {
        try {
            $user = $this->userRepository->getUserById($userId);
        } catch (Exception $e) {
            return null;
        }

        if ($user->getResetTokenExpiration() < $this->now
            || $user->getResetToken() !== $resetToken) {
            return null;
        }

        return $user;
    }

    public function completePasswordReset(
        string $userId,
        string $resetToken,
        string $password,
    ): ?User {
        $user = $this->verifyPasswordReset($userId, $resetToken);
        if ($user === null) {
            return null;
        }

        $user = $user
            ->withPassword($password)
            ->withResetToken(null)
            ->withResetTokenExpiration(null);

        return $this->persistUser($user);
    }
}
