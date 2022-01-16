<?php

namespace Elazar\Dibby\User;

use DateInterval;
use DateTimeImmutable;
use Elazar\Dibby\{
    Email\EmailService,
    Exception,
};
use Psr\Log\LoggerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordHasher $passwordHasher,
        private ResetTokenGenerator $resetTokenGenerator,
        private EmailService $emailService,
        private LoggerInterface $logger,
        private DateTimeImmutable $now,
        private string $resetTokenTimeToLive,
    ) { }

    public function persistUser(User $user): User
    {
        if (empty($user->getName())) {
            throw Exception::invalidInput('Name is required');
        }
        if (empty($user->getPassword())) {
            throw Exception::invalidInput('Password is required');
        }
        if (!filter_var($user->getEmail(), \FILTER_VALIDATE_EMAIL)) {
            throw Exception::invalidInput('E-mail is invalid');
        }
        /** @var string */
        $password = $user->getPassword();
        $passwordHash = $this->passwordHasher->getHash($password);
        $user = $user->withPasswordHash($passwordHash);
        return $this->userRepository->persistUser($user);
    }

    public function authenticateUser(string $email, string $password): ?User
    {
        try {
            $user = $this->userRepository->getUserByEmail($email);
        } catch (Exception $error) {
            $this->logger->warning('Failed to authenticate user', [
                'email' => $email,
                'error' => $error,
            ]);
            return null;
        }

        $passwordHash = $user->getPasswordHash();
        if ($passwordHash === null) {
            $this->logger->warning('User has no password hash', [
                'email' => $email,
            ]);
            return null;
        }

        return $this->passwordHasher->verifyHash($password, $passwordHash)
            ? $user : null;
    }

    public function startPasswordReset(string $email): ?User
    {
        try {
            $user = $this->userRepository->getUserByEmail($email);
        } catch (Exception $error) {
            $this->logger->warning('Failed to start password reset', [
                'email' => $email,
                'error' => $error,
            ]);
            return null;
        }

        /** @var string */
        $userId = $user->getId();
        $resetToken = $this->resetTokenGenerator->generateToken();

        $expirationInterval = new DateInterval($this->resetTokenTimeToLive);
        $resetTokenExpiration = $this->now->add($expirationInterval);

        $result = $this->emailService->sendPasswordResetEmail(
            toEmail: $email,
            userId: $userId,
            resetToken: $resetToken,
        );
        if ($result === false) {
            return null;
        }

        $user = $user
            ->withResetToken($resetToken)
            ->withResetTokenExpiration($resetTokenExpiration);

        return $this->userRepository->persistUser($user);
    }

    public function verifyPasswordReset(string $userId, string $resetToken): ?User
    {
        try {
            $user = $this->userRepository->getUserById($userId);
        } catch (Exception $error) {
            $this->logger->warning('Failed to verify password reset', [
                'user' => $userId,
                'token' => $resetToken,
                'error' => $error,
            ]);
            return null;
        }

        if ($user->getResetTokenExpiration() < $this->now
            || $user->getResetToken() !== $resetToken) {
            $this->logger->notice('Failed to verify password reset', [
                'user' => $userId,
                'token' => $resetToken,
                'now' => $this->now,
                'user_token' => $user->getResetToken(),
                'user_token_expiration' => $user->getResetTokenExpiration(),
            ]);
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
