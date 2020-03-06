<?php

namespace Tests\Unit;

use App\Factory\UserFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;
use App\Department;
use App\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\UserTransformable;

class UserTest extends TestCase
{

    use DatabaseTransactions, WithFaker, UserTransformable;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_can_show_all_the_users()
    {
        $inserteduser = factory(User::class)->create();
        $userRepo = new UserRepository(new User);
        $list = $userRepo->getActiveUsers()->toArray();
        $myLastElement = end($list);

        // $this->assertInstanceOf(Collection::class, $list);
        $this->assertEquals($myLastElement['first_name'], $inserteduser->first_name);
        $this->assertEquals($myLastElement['last_name'], $inserteduser->last_name);
        $this->assertEquals($myLastElement['email'], $inserteduser->email);
        $this->assertEquals($myLastElement['username'], $inserteduser->username);
    }

    /** @test */
    public function it_can_delete_the_user()
    {
        $user = factory(User::class)->create();
        $userRepo = new UserRepository($user);
        $deleted = $userRepo->deleteUser($user->id);
        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_update_the_user()
    {
        $user = factory(User::class)->create();
        $data = ['first_name' => $this->faker->firstName];
        $userRepo = new UserRepository($user);
        $updated = $userRepo->save($data, $user);
        $this->assertInstanceOf(User::class, $updated);
    }

    /** @test */
    public function it_can_show_the_user()
    {
        $user = factory(User::class)->create();
        $userRepo = new UserRepository(new User);
        $found = $userRepo->findUserById($user->id);
        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->name, $found->name);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $factory = (new UserFactory())->create();
        $data = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'username' => $this->faker->userName,
            'password' => $this->faker->password,
            'is_active' => 1,
            'profile_photo' => $this->faker->word,
        ];

        $userRepo = new UserRepository(new User);
        $user = $userRepo->save($data, $factory);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['first_name'], $user->first_name);
    }

    /**
     * @codeCoverageIgnore
     */
    public function it_errors_creating_the_user_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $product = new UserRepository(new User);
        $product->createUser([]);
    }

    /** @test */
    public function it_errors_finding_a_user()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $user = new UserRepository(new User);
        $user->findUserById(999);
    }

    /** @test */
    public function it_can_list_all_users()
    {
        factory(User::class, 5)->create();
        $userRepo = new UserRepository(new User);
        $list = $userRepo->listUsers();
        $this->assertInstanceOf(Collection::class, $list);
    }

    /** @test */
    public function it_can_attach_a_department()
    {
        $user = factory(User::class)->create();
        $department = factory(Department::class)->create();
        $userRepo = new UserRepository($user);
        $result = $userRepo->syncDepartment($user, $department->id);
        $this->assertArrayHasKey('attached', $result);
    }

    /** @test */
    public function it_can_attach_roles()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();
        $userRepo = new UserRepository($user);
        $result = $userRepo->syncRoles($user, [0 => [$role->id]]);
        $this->assertArrayHasKey('attached', $result);
    }

    /** @test */
    public function it_can_transform_user()
    {
        $user = factory(User::class)->create();

        $arrUser = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
            'is_active' => 1
        ];

        $user = factory(User::class)->create($arrUser);
        $transformed = $this->transformUser($user);

        $this->assertEquals($arrUser['first_name'], $transformed->first_name);
        $this->assertEquals($arrUser['last_name'], $transformed->last_name);
        $this->assertEquals($arrUser['username'], $transformed->username);
        $this->assertEquals($arrUser['email'], $transformed->email);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
