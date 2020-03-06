<?php

namespace Tests\Unit;

use App\ProductAttribute;
use App\Repositories\ProductAttributeRepository;
use App\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductAttributeUnitTest extends TestCase
{

    use WithFaker, DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_throws_error_when_the_product_attribute_is_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $productAttributeRepo = new ProductAttributeRepository(new ProductAttribute);
        $productAttributeRepo->findProductAttributeById(999);
    }

    /** @test */
    public function it_can_find_the_product_attribute_by_id()
    {
        $product = factory(Product::class)->create();
        $productAttribute = factory(ProductAttribute::class)->create([
            'product_id' => $product->id,
            'range_from' => 99.99,
            'range_to' => 200.00
        ]);

        $productAttributeRepo = new ProductAttributeRepository(new ProductAttribute);
        $found = $productAttributeRepo->findProductAttributeById($productAttribute->id);
        $this->assertEquals($productAttribute->range_from, $found->range_from);
        $this->assertEquals($productAttribute->range_to, $found->range_to);
    }

    /** @test */
    public function it_returns_null_deleting_non_existing_product_attribute()
    {
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $deleted = $productRepo->removeProductAttribute(new ProductAttribute, $product);
        $this->assertFalse($deleted);
    }

    /** @test */
    public function it_can_remove_product_attribute()
    {
        $data = [
            'range_from' => $this->faker->randomFloat(2),
            'range_to' => $this->faker->randomFloat(2),
            'payable_months' => 12,
            'interest_rate' => $this->faker->randomFloat(2)
        ];
        $productAttribute = new ProductAttribute($data);
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $created = $productRepo->saveProductAttributes($productAttribute, $product);
        $deleted = $productRepo->removeProductAttribute($created, $product);
        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_create_product_attribute()
    {
        $data = [
            'range_from' => $this->faker->randomFloat(2),
            'range_to' => $this->faker->randomFloat(2),
            'payable_months' => 12,
            'interest_rate' => $this->faker->randomFloat(2),
        ];
        $productAttribute = new ProductAttribute($data);
        $product = factory(Product::class)->create();
        $productRepo = new ProductRepository($product);
        $created = $productRepo->saveProductAttributes($productAttribute, $product);

        $this->assertInstanceOf(ProductAttribute::class, $created);
        $this->assertEquals($data['range_from'], $created->range_from);
        $this->assertEquals($data['range_to'], $created->range_to);
        $this->assertEquals($data['payable_months'], $created->payable_months);
        $this->assertEquals($data['interest_rate'], $created->interest_rate);
    }

}
