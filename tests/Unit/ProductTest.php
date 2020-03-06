<?php

namespace Tests\Unit;

use App\Company;
use App\Factory\ProductFactory;
use App\Filters\ProductFilter;
use App\Product;
use App\Repositories\ProductRepository;
use App\Requests\SearchRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\ProductTransformable;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use App\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ProductImageRepository;
use App\ProductImage;
use App\User;

class ProductTest extends TestCase
{

    use DatabaseTransactions, ProductTransformable, WithFaker;

    private $user;

    private $company;

    /**
     * @var int
     */
    private $account_id = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();

        $this->user = factory(User::class)->create();
        $this->company = factory(Company::class)->create();
    }

    /** @test */
    public function it_can_return_the_product_of_the_cover_image()
    {
        $thumbnails = [
            UploadedFile::fake()->image('cover.jpg', 600, 600),
            UploadedFile::fake()->image('cover.jpg', 600, 600),
            UploadedFile::fake()->image('cover.jpg', 600, 600)
        ];
        $collection = collect($thumbnails);
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $productRepo->saveProductImages($collection, $product);
        $images = $productRepo->findProductImages($product);

        $images->each(function (ProductImage $image) use ($product) {
            $productImageRepo = new ProductImageRepository($image);
            $foundProduct = $productImageRepo->findProduct();
            $this->assertInstanceOf(Product::class, $foundProduct);
            $this->assertEquals($product->name, $foundProduct->name);
            $this->assertEquals($product->slug, $foundProduct->slug);
            $this->assertEquals($product->description, $foundProduct->description);
            $this->assertEquals($product->quantity, $foundProduct->quantity);
            $this->assertEquals($product->price, $foundProduct->price);
            $this->assertEquals($product->status, $foundProduct->status);
        });
    }

    /** @test */
    public function it_can_save_the_thumbnails_properly_in_the_file_storage()
    {
        $thumbnails = [
            UploadedFile::fake()->image('cover.jpg', 600, 600),
            UploadedFile::fake()->image('cover.jpg', 600, 600),
            UploadedFile::fake()->image('cover.jpg', 600, 600)
        ];
        $collection = collect($thumbnails);
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $productRepo->saveProductImages($collection, $product);
        $images = $productRepo->findProductImages($product);

        $images->each(function (ProductImage $image) {
            $exists = Storage::disk('public')->exists($image->src);
            $this->assertTrue($exists);
        });
    }

    /** @test */
    public function it_can_save_the_cover_image_properly_in_file_storage()
    {
        $cover = UploadedFile::fake()->image('cover.jpg', 600, 600);
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $filename = $productRepo->saveCoverImage($cover);
        $exists = Storage::disk('public')->exists($filename);
        $this->assertTrue($exists);
    }

    /** @test */
    public function it_errors_when_the_slug_in_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $product = new ProductRepository(new Product);
        $product->findProductBySlug(['slug' => 'unknown']);
    }

    public function it_errors_creating_the_product_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $task = new ProductRepository(new Product);
        $task->createProduct([]);
    }

    /** @test */
    public function it_can_find_the_product_with_the_slug()
    {
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository(new Product);
        $found = $productRepo->findProductBySlug(['slug' => $product->slug]);
        $this->assertEquals($product->name, $found->name);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $deleted = $productRepo->newDelete($product);
        $this->assertTrue($deleted);
        //$this->assertDatabaseMissing('products', ['name' => $product->name]);
    }

    /** @test */
    public function it_can_list_all_the_products()
    {
        $product = factory(Product::class)->create();
        $attributes = $product->getFillable();
        $products =
            (new ProductFilter(new ProductRepository(new Product)))->filter(new SearchRequest(), $this->account_id);
        $this->assertNotEmpty($products);
        $this->assertInstanceOf(Product::class, $products[0]);
    }

    /** @test */
    public function it_errors_finding_a_product()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $product = new ProductRepository(new Product);
        $product->findProductById(999);
    }

    /** @test */
    public function it_can_find_the_product()
    {
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository(new Product);
        $found = $productRepo->findProductById($product->id);
        $this->assertInstanceOf(Product::class, $found);
        $this->assertEquals($product->sku, $found->sku);
        $this->assertEquals($product->name, $found->name);
        $this->assertEquals($product->slug, $found->slug);
        $this->assertEquals($product->description, $found->description);
        $this->assertEquals($product->price, $found->price);
        $this->assertEquals($product->status, $found->status);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $product = factory(Product::class)->create();
        $productName = 'apple';
        $data = [
            'account_id' => $this->account_id,
            'user_id' => $this->user->id,
            'sku' => '11111',
            'name' => $productName,
            'slug' => str_slug($productName),
            'description' => $this->faker->paragraph,
            'price' => 9.95,
            'status' => 1
        ];
        $productRepo = new ProductRepository($product);
        $updated = $productRepo->save($data, $product);
        $this->assertInstanceOf(Product::class, $updated);
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $factory = (new ProductFactory())->create($this->user->id, $this->account_id);
        $company = factory(Company::class)->create();

        $name = $this->faker->word;

        $params = [
            'company_id' => $company->id,
            'sku' => $this->faker->numberBetween(1111111, 999999),
            'name' => $name,
            'slug' => str_slug($name),
            'description' => $this->faker->paragraph,
            'price' => 9.95,
            'status' => 1,
        ];
        $product = new ProductRepository(new Product);
        $created = $product->save($params, $factory);
        $this->assertInstanceOf(Product::class, $created);
        $this->assertEquals($params['sku'], $created->sku);
        $this->assertEquals($params['name'], $created->name);
        $this->assertEquals($params['slug'], $created->slug);
        $this->assertEquals($params['description'], $created->description);
        $this->assertEquals($params['price'], $created->price);
        $this->assertEquals($params['status'], $created->status);
    }

    /** @test */
    public function it_can_delete_a_thumbnail_image()
    {
        $product = 'apple';
        $cover = UploadedFile::fake()->image('file.png', 600, 600);
        $params = [
            'account_id' => $this->account_id,
            'sku' => $this->faker->numberBetween(1111111, 999999),
            'name' => $product,
            'slug' => str_slug($product),
            'description' => $this->faker->paragraph,
            'cover' => $cover,
            'quantity' => 10,
            'price' => 9.95,
            'company_id' => $this->company->id,
            'status' => 1,
            'image' => [
                UploadedFile::fake()->image('file.png', 200, 200),
                UploadedFile::fake()->image('file1.png', 200, 200),
                UploadedFile::fake()->image('file2.png', 200, 200)
            ]
        ];
        $productRepo = new ProductRepository(new Product);
        $factory = (new ProductFactory())->create($this->user->id, $this->account_id);
        $created = $productRepo->save($params, $factory);
        //$repo->saveProductImages(collect($params['image']), $created);
        $thumbnails = $productRepo->findProductImages($created);
        $this->assertCount(3, $thumbnails);

        $thumbnails->each(function ($thumbnail) {
            $repo = new ProductRepository(new Product());
            $repo->deleteThumb($thumbnail->src);
        });

        $this->assertCount(0, $productRepo->findProductImages($created));
    }

    /** @test */
    public function it_can_show_all_the_product_images()
    {
        $product = 'apple';
        $cover = UploadedFile::fake()->image('file.png', 600, 600);
        $params = [
            'account_id' => $this->account_id,
            'sku' => $this->faker->numberBetween(1111111, 999999),
            'name' => $product,
            'slug' => str_slug($product),
            'description' => $this->faker->paragraph,
            'cover' => $cover,
            'quantity' => 10,
            'price' => 9.95,
            'status' => 1,
            'company_id' => $this->company->id,
            'image' => [
                UploadedFile::fake()->image('file.png', 200, 200),
                UploadedFile::fake()->image('file1.png', 200, 200),
                UploadedFile::fake()->image('file2.png', 200, 200)
            ]
        ];

        $factory = (new ProductFactory())->create($this->user->id, $this->account_id);
        $productRepo = new ProductRepository(new Product);
        $created = $productRepo->save($params, $factory);
        $repo = new ProductRepository($created);
        //$repo->saveProductImages(collect($params['image']), $created);
        $this->assertCount(3, $repo->findProductImages($created));
    }

    /** @test */
    public function it_can_delete_the_file_only_by_updating_the_database()
    {
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $this->assertTrue($productRepo->deleteFile(['product' => $product->id]));
    }

    /** @test */
    public function it_can_detach_all_the_categories()
    {
        $product = factory(Product::class)->create();
        $categories = factory(Category::class, 4)->create();
        $productRepo = new ProductRepository($product);
        $ids = $categories->transform(function (Category $category) {
            return $category->id;
        })->all();
        $productRepo->syncCategories($ids, $product);
        $this->assertCount(4, $productRepo->getCategories());
        $productRepo->detachCategories($product);
        $this->assertCount(0, $productRepo->getCategories());
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
