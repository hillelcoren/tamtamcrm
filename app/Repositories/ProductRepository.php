<?php

namespace App\Repositories;

use App\Repositories\Base\BaseRepository;
use App\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Exception;
use Illuminate\Support\Collection as Support;
use Illuminate\Database\Eloquent\Collection;
use App\Task;
use App\Brand;
use App\Category;
use App\ProductImage;
use App\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Traits\UploadableTrait;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{

    use UploadableTrait;

    /**
     * ProductRepository constructor.
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);
        $this->model = $product;
    }

    /**
     * List all the products
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Support
    {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Find the product by ID
     *
     * @param int $id
     *
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findProductById(int $id): Product
    {
        return $this->findOneOrFail($id);
    }

    /**
     * Delete the product
     *
     * @param Product $product
     *
     * @return bool
     * @throws Exception
     */
    public function deleteProduct(): bool
    {
        return $this->delete();
    }

    /**
     * Get the product via slug
     *
     * @param array $slug
     *
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findProductBySlug(array $slug): Product
    {
        return $this->findOneByOrFail($slug);
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Detach the categories
     */
    public function detachCategories(Product $product)
    {
        $product->categories()->detach();
    }

    /**
     * Return the categories which the product is associated with
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->model->categories()->get();
    }

    /**
     * Sync the categories
     *
     * @param array $params
     */
    public function syncCategories(array $params, Product $product)
    {
        $product->categories()->sync($params);
    }

    /**
     * @return Brand
     */
    public function findBrand()
    {
        return $this->model->brand;
    }

    /**
     *
     * @param Brand $objBrand
     * @return Support
     */
    public function filterProductsByBrand(Brand $objBrand): Support
    {
        return $this->model->where('company_id', $objBrand->id)->get();
    }

    /**
     *
     * @param Category $objCategory
     * @return Support
     */
    public function filterProductsByCategory(Category $objCategory): Support
    {

        return $this->model->join('category_product', 'category_product.product_id', '=', 'products.id')
                           ->select('products.*')->where('category_product.category_id', $objCategory->id)
                           ->groupBy('products.id')->get();
    }

    /**
     * Delete the attribute from the product
     *
     * @param ProductAttribute $productAttribute
     *
     * @return bool|null
     * @throws Exception
     */
    public function removeProductAttribute(ProductAttribute $productAttribute, Product $product): ?bool
    {
        return $product->attributes()->delete();
    }

    /**
     * List all the product attributes associated with the product
     *
     * @return Collection
     */
    public function listProductAttributes(): Collection
    {
        return $this->model->attributes()->get();
    }

    /**
     * Associate the product attribute to the product
     *
     * @param ProductAttribute $productAttribute
     * @return ProductAttribute
     */
    public function saveProductAttributes(ProductAttribute $productAttribute, Product $product): ProductAttribute
    {

        $product->attributes()->updateOrCreate(['product_id' => $product->id], $productAttribute->toArray());

        return $productAttribute;
    }

    /**
     *
     * @param Category $category
     * @param type $value
     */
    public function getProductsByDealValueAndCategory(Category $category, Request $request): Support
    {
        return $this->model->join('product_attributes', 'product_attributes.product_id', '=', 'products.id')
                           ->join('category_product', 'category_product.product_id', '=', 'products.id')
                           ->select('products.*')->where('product_attributes.range_from', '<', $request->valued_at)
                           ->where('product_attributes.range_to', '>', $request->valued_at)
                           ->where('products.status', '=', 1)->where('category_product.category_id', '=', $category->id)
                           ->get();
    }

    /**
     * @param $file
     * @param null $disk
     * @return bool
     */
    public function deleteFile(array $file, $disk = null): bool
    {
        return $this->update(['cover' => null], $file['product']);
    }

    /**
     * @param string $src
     * @return bool
     */
    public function deleteThumb(string $src): bool
    {
        return DB::table('product_images')->where('src', $src)->delete();
    }

    /**
     * @return mixed
     */
    public function findProductImages(Product $product): Collection
    {
        return $product->images()->get();
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function saveCoverImage(UploadedFile $file): string
    {
        return $file->store('products', ['disk' => 'public']);
    }

    /**
     * @param Support $collection
     * @param Product $product
     * @return bool
     */
    public function saveProductImages(Support $collection, Product $product): bool
    {
        $collection->each(function (UploadedFile $file) use ($product) {
            $filename = $this->storeFile($file);
            $productImage = new ProductImage([
                'product_id' => $this->model->id,
                'src' => $filename
            ]);
            $product->images()->save($productImage);
        });

        return true;
    }

    public function save($data, Product $product): ?Product
    {
        $this->data['slug'] = str_slug($data['name']);

        if (isset($data['cover']) && $data['cover'] instanceof UploadedFile) {
            $data['cover'] = $this->saveCoverImage($data['cover']);
        }

        $product->fill($data);
        $product->save();

        if (isset($data['image'])) {
            $this->saveProductImages(collect($data['image']), $product);
        }

        if (isset($data['category']) && !empty($data['category'])) {
            $categories = !is_array($data['category']) ? explode(',', $data['category']) : $data['category'];
            $this->syncCategories($categories, $product);
        } else {
            $this->detachCategories($product);
        }

        return $product;
    }


}
