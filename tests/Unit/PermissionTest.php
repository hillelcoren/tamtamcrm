<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\User;
use App\Task;
use Illuminate\Foundation\Testing\WithFaker;
use App\Role;
use App\Permission;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PermissionTest extends TestCase
{

    use DatabaseTransactions, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_can_attach_permission_to_role()
    {
        $role = factory(Role::class)->create();
        $permission = factory(Permission::class)->create();
        $roleRepo = new RoleRepository($role);
        $roleRepo->attachToPermission($permission);
        $attachedPermissions = $roleRepo->listPermissions();
        $attachedPermissions->each(function (Permission $item) use ($permission) {
            $this->assertEquals($permission->name, $item->name);
        });
    }

    /** @test */
    public function it_can_list_all_permissions()
    {
        factory(Permission::class, 5)->create();
        $permissionRepo = new PermissionRepository(new Permission);
        $list = $permissionRepo->listPermissions();
        $this->assertInstanceOf(Collection::class, $list);
    }

    /** @test */
    public function it_can_delete_permission()
    {
        $permission = factory(Permission::class)->create();
        $permissionRepo = new PermissionRepository($permission);
        $deleted = $permissionRepo->deletePermissionById($permission->id);
        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_update_the_permission()
    {
        $permission = factory(Permission::class)->create();
        $data = [
            'name' => 'can-view',
        ];
        $permissionRepo = new PermissionRepository($permission);
        $updated = $permissionRepo->updatePermission($data);
        $found = $permissionRepo->findPermissionById($permission->id);
        $this->assertTrue($updated);
        $this->assertEquals($data['name'], $found->name);
    }

    /** @test */
    public function it_can_show_the_permission()
    {
        $permission = factory(Permission::class)->create();
        $permissionRepo = new PermissionRepository(new Permission);
        $found = $permissionRepo->findPermissionById($permission->id);
        $this->assertInstanceOf(Permission::class, $found);
        $this->assertEquals($permission->name, $found->name);
    }

    /** @test */
    public function it_can_create_permission()
    {
        $data = [
            'name' => 'can-view-employee-list',
            'description' => 'can view permission'
        ];
        $permissionRepo = new PermissionRepository(new Permission);
        $permission = $permissionRepo->createPermission($data);
        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($data['name'], $permission->name);
        $this->assertEquals($data['description'], $permission->description);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
