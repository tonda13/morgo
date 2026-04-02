<?php

declare(strict_types=1);

namespace Morgo\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Domain\User\User;
use Morgo\Domain\User\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function findById(int $id): ?User
    {
        $row = $this->capsule->table('users')->find($id);
        return $row ? User::fromArray((array) $row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->capsule->table('users')->where('email', strtolower($email))->first();
        return $row ? User::fromArray((array) $row) : null;
    }

    public function save(User $user): User
    {
        $data = [
            'email'         => $user->email,
            'password_hash' => $user->password_hash,
            'display_name'  => $user->display_name,
            'role'          => $user->role,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        if (isset($user->id) && $user->id > 0) {
            $this->capsule->table('users')->where('id', $user->id)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $user->id = (int) $this->capsule->table('users')->insertGetId($data);
        }

        return $user;
    }

    public function delete(int $id): void
    {
        $this->capsule->table('users')->where('id', $id)->delete();
    }

    public function findAll(): array
    {
        return $this->capsule->table('users')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($row) => User::fromArray((array) $row))
            ->all();
    }
}
