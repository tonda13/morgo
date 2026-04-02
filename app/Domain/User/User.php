<?php

declare(strict_types=1);

namespace Morgo\Domain\User;

class User
{
    public int $id;
    public string $email;
    public string $password_hash;
    public string $display_name;
    public string $role;
    public ?\DateTimeImmutable $last_login;
    public \DateTimeImmutable $created_at;

    public function getRole(): Role
    {
        return Role::from($this->role);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin->value;
    }

    public function isEditor(): bool
    {
        return $this->role === Role::Editor->value;
    }

    public static function fromArray(array $data): self
    {
        $user               = new self();
        $user->id           = (int) $data['id'];
        $user->email        = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->display_name = $data['display_name'];
        $user->role         = $data['role'] ?? Role::Editor->value;
        $user->last_login   = isset($data['last_login'])
            ? new \DateTimeImmutable($data['last_login'])
            : null;
        $user->created_at   = new \DateTimeImmutable($data['created_at'] ?? 'now');
        return $user;
    }
}
