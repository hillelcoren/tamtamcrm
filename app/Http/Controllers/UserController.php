<?php

namespace App\Http\Controllers;

use App\Factory\UserFactory;
use App\Filters\UserFilter;
use App\User;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Requests\User\CreateUserRequest;
use App\Requests\User\UpdateUserRequest;
use App\Repositories\UserRepository;
use App\Transformations\UserTransformable;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use App\Repositories\DepartmentRepository;
use App\Department;
use App\Requests\SearchRequest;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    use UserTransformable;

    /**
     * @var UserRepositoryInterface
     */
    private $user_repo;

    /**
     * @var RoleRepositoryInterface
     */
    private $role_repo;

    /**
     * UserController constructor.
     * @param UserRepositoryInterface $user_repo
     * @param RoleRepositoryInterface $role_repo
     */
    public function __construct(UserRepositoryInterface $user_repo, RoleRepositoryInterface $role_repo)
    {
        $this->user_repo = $user_repo;
        $this->role_repo = $role_repo;
    }

    public function index(SearchRequest $request)
    {
        $users = (new UserFilter($this->user_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($users);
    }

    public function dashboard()
    {

        return view('index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateUserRequest $request
     *
     * @return Response
     */
    public function store(CreateUserRequest $request)
    {
        $user = $this->user_repo->save($request->all(), (new UserFactory())->create());
        return $this->transformUser($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit(int $id)
    {
        $user = $this->user_repo->findUserById($id);
        $roles = $this->role_repo->listRoles('created_at', 'desc');
        $arrData = [
            'user' => $this->transformUser($user),
            'roles' => $roles,
            'selectedIds' => $user->roles()->pluck('role_id')->all()
        ];

        return response()->json($arrData);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function archive(int $id)
    {
        $objUser = $this->user_repo->findUserById($id);
        $response = $objUser->delete();

        if ($response) {
            return response()->json('User deleted!');
        }

        return response()->json('User could not be deleted!');
    }

    public function destroy(int $id)
    {
        $user = $this->user_repo->findUserById($id);
        $this->user_repo->destroy($user);
        return response()->json([], 200);
    }

    /**
     * @param UpdateUserRequest $request
     * @param $id
     *
     * @return Response
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $user = $this->user_repo->findUserById($id);
        $user = $this->user_repo->save($request->all(), $user);
        return response()->json($user);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function upload(Request $request)
    {
        if ($request->hasFile('file') && $request->file('file') instanceof UploadedFile) {
            $user = auth()->user();
            $userRepo = new UserRepository($user);
            $data['profile_photo'] = $this->user_repo->saveUserImage($request->file('file'));
            $userRepo->updateUser($data);
        }

        return response()->json('file uploaded successfully');
    }

    /**
     * return a user based on username
     * @param string $username
     * @return type
     */
    public function profile(string $username)
    {
        $user = $this->user_repo->findUserByUsername($username);
        return response()->json($user);
    }

    /**
     * @param int $department_id
     * @return mixed
     */
    public function filterUsersByDepartment(int $department_id)
    {
        $objDepartment = (new DepartmentRepository(new Department))->findDepartmentById($department_id);
        $users = $this->user_repo->getUsersForDepartment($objDepartment);
        return response()->json($users);
    }

    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $users = User::withTrashed()->find($ids);
        $users->each(function ($user, $key) use ($action) {
            $this->user_repo->{$action}($user);
        });
        return response()->json(User::withTrashed()->whereIn('id', $ids));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $user = $this->user_repo->findUserById($id);
        return response()->json($this->transformUser($user));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = User::withTrashed()->where('id', '=', $id)->first();
        $this->user_repo->restore($group);
        return response()->json([], 200);
    }

}
