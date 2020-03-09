<?php

namespace App\Repositories;

use App\Account;
use App\DataMapper\CompanySettings;
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

        /*************** save new user ***************************/
        $user->fill($data);
        $user->save();

if (isset($data['company_user'])) {
    $account = Account::find($data['company_user']['account_id']);

    $domain_id = $account->domains->id;

    $cu = AccountUser::whereUserId($user->id)->whereAccountId($account->id)->withTrashed()->first();

    /*No company user exists - attach the user*/
    if (!$cu) {
        $data['company_user']['domain_id'] = $domain_id;

        if(empty($data['company_user']['notifications'])) {
            $data['company_user']['notifications'] = CompanySettings::notificationDefaults();
        }

        $user->accounts()->attach($account->id, $data['company_user']);
    } else {
        $cu->fill($data['company_user']);
        $cu->restore();
        $cu->save();
    }

    $user->with(['account_users' => function ($query) use($account, $user){
        $query->whereAccountId($account->id)
            ->whereUserId($user->id);
    }])->first();
    //return $user->with('company_user')->whereCompanyId($company->id)->first();
}


        if (isset($data['role']) && !empty($data['role'])) {
            $this->syncRoles($user, [$data['role']]);
        }

        if (isset($data['department']) && !empty($data['department'])) {
            $this->syncDepartment($user, $data['department']);
        }
        return $user->fresh();

    }

    /**
     * @param array $data
     * @param User $user
     * @return User|null
     * @throws Exception
     */
    public function destroy(User $user, array $data = [])
    {
        if (!empty($data) && array_key_exists('account_user', $data)) {
            $company = auth()->user()->account_user();

            $cu = AccountUser::whereUserId($user->id)->whereAccountId($company->id)->first();
            $cu->delete();
        } else {
            $user->delete();
        }

        return $user->fresh();
    }

}
