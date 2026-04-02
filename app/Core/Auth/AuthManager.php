<?php

declare(strict_types=1);

namespace Morgo\Core\Auth;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Domain\User\User;
use Morgo\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

class AuthManager
{
    private const SESSION_KEY      = '_morgo_user_id';
    private const MAX_ATTEMPTS     = 5;
    private const LOCKOUT_MINUTES  = 15;
    private const REMEMBER_DAYS    = 30;
    private const REMEMBER_COOKIE  = 'morgo_remember';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly Capsule $capsule,
        private readonly LoggerInterface $logger
    ) {
    }

    public function login(string $email, string $password, bool $remember = false): bool
    {
        $ip = $this->getClientIp();

        if ($this->isLockedOut($email, $ip)) {
            $this->logger->warning('Login blocked (lockout)', ['email' => $email, 'ip' => $ip]);
            return false;
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, $user->password_hash)) {
            $this->recordFailedAttempt($email, $ip);
            $this->logger->warning('Failed login', ['email' => $email, 'ip' => $ip]);
            return false;
        }

        // Úspěšné přihlášení — smazat neúspěšné pokusy
        $this->clearAttempts($email, $ip);

        // Regenerovat session ID (security)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = $user->id;

        // Aktualizovat last_login
        $this->capsule->table('users')
            ->where('id', $user->id)
            ->update(['last_login' => date('Y-m-d H:i:s')]);

        // Remember me
        if ($remember) {
            $this->setRememberToken($user->id);
        }

        $this->logger->info('User logged in', ['user_id' => $user->id, 'ip' => $ip]);
        return true;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION[self::SESSION_KEY] ?? null;

        // Smazat remember token
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            $token = $_COOKIE[self::REMEMBER_COOKIE];
            $this->capsule->table('remember_tokens')
                ->where('token_hash', hash('sha256', $token))
                ->delete();
            setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/', '', true, true);
        }

        unset($_SESSION[self::SESSION_KEY]);
        session_destroy();

        if ($userId) {
            $this->logger->info('User logged out', ['user_id' => $userId]);
        }
    }

    public function getCurrentUser(): ?User
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION[self::SESSION_KEY] ?? null;

        if ($userId === null) {
            // Zkusit remember me cookie
            $userId = $this->checkRememberToken();
        }

        if ($userId === null) {
            return null;
        }

        return $this->users->findById((int) $userId);
    }

    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function isLockedOut(string $email, ?string $ip = null): bool
    {
        $ip ??= $this->getClientIp();
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::LOCKOUT_MINUTES . ' minutes'));

        $count = $this->capsule->table('login_attempts')
            ->where('email', $email)
            ->where('attempted_at', '>=', $cutoff)
            ->count();

        return $count >= self::MAX_ATTEMPTS;
    }

    public function getRemainingAttempts(string $email): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::LOCKOUT_MINUTES . ' minutes'));

        $count = $this->capsule->table('login_attempts')
            ->where('email', $email)
            ->where('attempted_at', '>=', $cutoff)
            ->count();

        return max(0, self::MAX_ATTEMPTS - $count);
    }

    private function recordFailedAttempt(string $email, string $ip): void
    {
        $this->capsule->table('login_attempts')->insert([
            'email'        => $email,
            'ip'           => $ip,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function clearAttempts(string $email, string $ip): void
    {
        $this->capsule->table('login_attempts')
            ->where('email', $email)
            ->delete();
    }

    private function setRememberToken(int $userId): void
    {
        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expires   = date('Y-m-d H:i:s', strtotime('+' . self::REMEMBER_DAYS . ' days'));

        $this->capsule->table('remember_tokens')->insert([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        setcookie(
            self::REMEMBER_COOKIE,
            $token,
            ['expires' => strtotime('+' . self::REMEMBER_DAYS . ' days'), 'path' => '/', 'httponly' => true, 'samesite' => 'Strict', 'secure' => config('app.session.secure', false)]
        );
    }

    private function checkRememberToken(): ?int
    {
        $token = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if ($token === null) {
            return null;
        }

        $tokenHash = hash('sha256', $token);
        $row = $this->capsule->table('remember_tokens')
            ->where('token_hash', $tokenHash)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if ($row === null) {
            return null;
        }

        // Nastavit session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = $row->user_id;

        return (int) $row->user_id;
    }

    private function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }
}
