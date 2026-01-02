<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findOrFail(int $id): User
    {
        $user = $this->findById($id);
        
        if (!$user) {
            throw new ModelNotFoundException("User not found with ID: {$id}");
        }
        
        return $user;
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        $user->tokens()->delete();
    }
}