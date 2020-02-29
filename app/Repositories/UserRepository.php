<?php

namespace App\Repositories;

use App\Account;
use App\User;
use App\Department;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use App\Exceptions\CreateUserErrorException;
use Exception;
use Illuminate\Support\Collection as Support;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use App\AccountUser;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{

    /**
     * UserRepository constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->model = $user;
    }

    /**
     * @param int $id
     *
     * @return User
     * @throws Exception
     */
    public function findUserById(int $id): User
    {
        return $this->findOneOrFail($id);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteUser(): bool
    {
        return $this->delete();
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     *
     * @return Collection
     */
    public function listUsers($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Support
    {
        return $this->all($columns, $orderBy, $sortBy);
    }

    /**
     *
     * @param type $columns
     * @param string $orderBy
     * @param string $sortBy
     * @return type
     */
    public function getActiveUsers($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Collection
    {

        return User::where('is_active', 1)->orderBy($orderBy, $sortBy)->get();
    }

    /**
     * @param array $roleIds
     */
    public function syncRoles(User $user, array $roleIds)
    {

        $mappedObjects = [];

        foreach ($roleIds[0] as $roleId) {
            $mappedObjects[] = $roleId;
        }

        return $user->roles()->sync($mappedObjects);
    }

    /**
     *
     * @param string $username
     * @return User
     */
    public function findUserByUsername(string $username): User
    {
        return $this->model->where('username', $username)->first();
    }

    /**
     *
     * @param string $username
     * @return User
     */
    public function getUsersForDepartment(Department $objDepartment): Support
    {
        return $this->model->join('department_user', 'department_user.user_id', '=', 'users.id')->select('users.*')
            ->where('department_user.department_id', $objDepartment->id)->groupBy('users.id')->get();
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function saveUserImage(UploadedFile $file): string
    {
        return $file->store('users', ['disk' => 'public']);
    }

    /**
     * Sync the categories
     *
     * @param array $params
     */
    public function syncDepartment(User $user, int $department_id)
    {
        return $user->departments()->sync($department_id);
    }

    /**
     * Saves the user and its contacts
     *
     * @param array $data The data
     * @param \App\Models\user $user The user
     *
     * @return     user|\App\Models\user|null  user Object
     */
    public function save(array $data, User $user): ?User
    {

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

$user->fill($data);
$user->save();

if (isset($data['company_user'])) {
    $company_users = collect($data['company_user']);
} else {
    $company_users = collect();
}

$user->account_users->pluck('account_id')->diff($company_users->pluck('account_id'))->each(function ($co_user) use (
    $user
) {
    AccountUser::whereAccountId($co_user)->whereUserId($user->id)->first()->forceDelete();
});

if (isset($data['company_user'])) {
    foreach ($data['company_user'] as $company_user) {
        if (auth()->user()->account_user()->count() > 0) {
            $account = Account::find($company_user['account_id']);

            $cu = AccountUser::whereUserId($user->id)->whereAccountId($account->id)->first();

            /*No company user exists - attach the user*/
            if (!$cu) {
                $user->accounts()->attach($account->id, $company_user);
            } else {
                $cu->fill($company_user);
                $cu->save();
            }
        }
    }


}


if (isset($data['role']) && !empty($data['role'])) {
    $this->syncRoles($user, [$data['role']]);
}

if (isset($data['department']) && !empty($data['department'])) {
    $this->syncDepartment($user, $data['department']);
}
return $user->fresh();

}

}
