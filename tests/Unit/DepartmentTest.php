<?php

namespace Tests\Unit;

use App\Department;
use App\User;
use App\Repositories\DepartmentRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\DepartmentTransformable;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;

class DepartmentTest extends TestCase
{

    use DatabaseTransactions, DepartmentTransformable, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_can_transform_the_department()
    {
        $department = factory(Department::class)->create();
        $repo = new DepartmentRepository($department);
        $departmentFromDb = $repo->findDepartmentById($department->id);
        $cust = $this->transformDepartment($department);
        //$this->assertInternalType('string', $departmentFromDb->status);
        $this->assertInternalType('string', $cust->name);
    }

    /** @test */
    public function it_can_delete_a_department()
    {
        $department = factory(Department::class)->create();
        $departmentRepo = new DepartmentRepository($department);
        $delete = $departmentRepo->deleteDepartment();
        $this->assertTrue($delete);
        //$this->assertDatabaseHas('departments', $department->toArray());
    }

    /** @test */
    public function it_fails_when_the_department_is_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $department = new DepartmentRepository(new Department);
        $department->findDepartmentById(999);
    }

    /** @test */
    public function it_can_find_a_department()
    {
        $user = factory(User::class)->create();
        $data = [
            'name' => $this->faker->name,
            'department_manager' => $user->id,
        ];

        $department = new DepartmentRepository(new Department);
        $created = $department->createDepartment($data);
        $found = $department->findDepartmentById($created->id);
        $this->assertInstanceOf(Department::class, $found);
        $this->assertEquals($data['name'], $found->name);
        $this->assertEquals($data['department_manager'], $found->department_manager);
    }

    /** @test */
    public function it_can_update_the_department()
    {
        $department = factory(Department::class)->create();
        $user = factory(User::class)->create();
        //$parent = factory(Category::class)->create();
        $params = [
            'name' => $this->faker->name,
            'department_manager' => $user->id,
            'parent' => 0,
        ];

        $departmentRepo = new DepartmentRepository($department);
        $updated = $departmentRepo->updateDepartment($params);
        $this->assertInstanceOf(Department::class, $updated);
        $this->assertEquals($params['name'], $updated->name);
        $this->assertEquals($params['department_manager'], $updated->department_manager);
        $this->assertEquals($params['parent'], $updated->parent_id);
    }

    /** @test */
    public function it_can_create_a_department()
    {

        $user = factory(User::class)->create();

        $data = [
            'name' => $this->faker->name,
            'department_manager' => $user->id
        ];
        $department = new DepartmentRepository(new Department);
        $created = $department->createDepartment($data);
        $this->assertInstanceOf(Department::class, $created);
        $this->assertEquals($data['name'], $created->name);
        $this->assertEquals($data['department_manager'], $created->department_manager);
        $collection = collect($data);
        $this->assertDatabaseHas('departments', $collection->all());
    }

    public function it_errors_creating_the_department_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $task = new DepartmentRepository(new Department);
        $task->createDepartment([]);
    }

    /** @test */
    public function it_can_list_all_departments()
    {
        factory(Department::class, 5)->create();
        $departmentRepo = new DepartmentRepository(new Department);
        $list = $departmentRepo->listDepartments();
        $this->assertInstanceOf(Collection::class, $list);
    }

    /** @test */
    public function it_can_create_root_department()
    {
        $user = factory(User::class)->create();

        $params = [
            'name' => $this->faker->name,
            'department_manager' => $user->id,
        ];

        $department = new DepartmentRepository(new Department);
        $created = $department->createDepartment($params);

        $this->assertTrue($created->isRoot());
    }

    /** @test */
    public function it_can_update_child_department_to_root_category()
    {
        // suppose to have a child category
        $parent = factory(Department::class, 1)->create();
        $child = factory(Department::class, 1)->create();
        $child[0]->parent()->associate($parent[0])->save();
        // send params without parent
        $department = new DepartmentRepository($child[0]);
        $updated = $department->updateDepartment([
            'name' => 'Boys',
        ]);
        // check if updated category is root
        $this->assertTrue($updated->isRoot());
    }

    /** @test */
    public function it_can_update_root_category_to_child()
    {
        $child = factory(Department::class)->create();
        $parent = factory(Department::class)->create();

        // set parent category via repository
        $department = new DepartmentRepository($child);
        $updated = $department->updateDepartment([
            'name' => $this->faker->name,
            'parent_id' => $parent->id
        ]);

        // check if updated category is root
        $this->assertTrue($updated->parent->is($parent));
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
