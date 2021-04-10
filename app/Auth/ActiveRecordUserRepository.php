<?php

namespace App\Auth;

use App\Auth\Models\User;
use PgFramework\Auth\User as AuthUser;
use PgFramework\Auth\Repository\UserRepositoryInterface;

class ActiveRecordUserRepository implements UserRepositoryInterface
{
    /**
     *
     * @var User
     */
    protected $model = User::class;

    public function getUser(string $field, $value): ?AuthUser
    {
        try {
            $user = $this->model::find(['conditions' => ["$field = ?", $value]]);
        } catch (\Exception $e) {
            return null;
        }
        if ($user) {
            return $user;
        }
        return null;
    }
}
