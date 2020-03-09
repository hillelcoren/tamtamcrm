<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Requests\CreateRoleRequest;
use App\Requests\UpdateRoleRequest;
use App\Role;
use App\Transformations\RoleTransformable;
use App\Requests\SearchRequest;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RoleController extends Controller
{

    use RoleTransformable;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepo;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    /**
     * RoleController constructor.
     *
     * @param RoleRepositoryInterface $roleRepository
     * @param PermissionRepositoryInterface $permissionRepository
     */
    public function __construct(RoleRepositoryInterface $roleRepository,
        PermissionRepositoryInterface $permissionRepository)
    {
        $this->roleRepo = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @return Factory|View
     */
    public function index(SearchRequest $request)
    {

        $orderBy = !$request->column ? 'name' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;

        if (request()->has('search_term') && !empty($request->search_term)) {
            $list = $this->roleRepo->searchRole(request()->input('search_term'));
        } else {
            $list = $this->roleRepo->listRoles($orderBy, $orderDir);
        }

        $roles = $list->map(function (Role $role) {
            return $this->transformRole($role);
        })->all();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->roleRepo->paginateArrayResults($roles, $recordsPerPage);
            return $paginatedResults->toJson();
        }

        return collect($roles)->toJson();
    }

    /**
     * @param CreateRoleRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateRoleRequest $request)
    {
        $roleObj = $this->roleRepo->createRole($request->except('_method', '_token'));

        if ($request->has('permissions')) {
            $roleRepo = new RoleRepository($roleObj);
            $roleRepo->syncPermissions($request->input('permissions'));
        }

        $role = $this->transformRole($roleObj);
        return $role->toJson();
    }

    /**
     * @param $id
     *
     * @return Factory|View
     */
    public function edit($id)
    {
        $role = $this->roleRepo->findRoleById($id);
        $roleRepo = new RoleRepository($role);
        $attachedPermissionsArrayIds = $roleRepo->listPermissions()->pluck('id')->all();
        $permissions = $this->permissionRepository->listPermissions(['id', 'name'], 'name', 'asc');

        $arrData = [
            'permissions' => $permissions->toArray(),
            'role' => $role->toArray(),
            'attachedPermissions' => $attachedPermissionsArrayIds
        ];

        return response()->json($arrData);
    }

    /**
     * @param UpdateRoleRequest $request
     * @param $id
     *
     * @return Factory|View
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        $role = $this->roleRepo->findRoleById($id);

        if ($request->has('permissions')) {
            $roleRepo = new RoleRepository($role);
            $roleRepo->syncPermissions($request->input('permissions'));
        }

        $update = new RoleRepository($role);
        $data = $request->except('_method', '_token', 'permissions');
        $update->updateRole($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(int $id)
    {

        $role = $this->roleRepo->findRoleById($id);

        $roleRepo = new RoleRepository($role);
        $roleRepo->deleteRoleById();
        return response()->json('Customer deleted!');
    }

}
