<?php

namespace App\Transformations;

use App\AccountUser;
use App\User;
use App\Repositories\DepartmentRepository;
use App\Department;

trait UserTransformable
{

    /**
     * Transform the user
     *
     * @param User $user
     * @return User
     */
    protected function transformUser(User $user)
    {
        $prop = new User;

        $prop->id = (int)$user->id;
        $prop->first_name = $user->first_name;
        $prop->last_name = $user->last_name;
        $prop->email = $user->email;
        $prop->username = $user->username;
        $prop->phone_number = $user->phone_number;
        $prop->password = $user->password;
        $prop->job_description = $user->job_description;
        $prop->account_users = $this->transformUserAccounts($user->account_users);
        $prop->gender = $user->gender;
        $prop->dob = $user->dob;
        $prop->department = 0;
        $prop->custom_value1 = $user->custom_value1 ?: '';
        $prop->custom_value2 = $user->custom_value2 ?: '';
        $prop->custom_value3 = $user->custom_value3 ?: '';
        $prop->custom_value4 = $user->custom_value4 ?: '';
        $prop->deleted_at = $user->deleted_at;
        $prop->created_at = $user->created_at;

        if ($user->departments->count() > 0) {
            $objDepartment = $user->departments->first();
            $prop->department = $objDepartment->id;
            $prop->dept = $objDepartment->name;
        }

        return $prop;
    }

    private function transformUserAccounts($account_users)
    {
        if (empty($account_users)) {
            return [];
        }

        return $account_users->map(function (AccountUser $account_user) {
            return (new AccountUserTransformable())->transform($account_user);
        })->all();
    }

}
