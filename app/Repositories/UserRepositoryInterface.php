<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function findOrFail(int $id): User;
    public function updatePassword(User $user, string $newPassword): void;
}
