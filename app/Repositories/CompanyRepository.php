<?php

namespace App\Repositories;

use App\Company;
use App\Repositories\Base\BaseRepository;
use App\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{

    private $contact_repo;

    /**
     * CompanyRepository constructor.
     *
     * @param Company $company
     */
    public function __construct(Company $company, CompanyContactRepository $contact_repo)
    {
        parent::__construct($company);
        $this->model = $company;
        $this->contact_repo = $contact_repo;
    }

    /**
     * @param int $id
     *
     * @return Brand
     */
    public function findBrandById(int $id): Company
    {
        return $this->findOneOrFail($id);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteBrand(): bool
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
    public function listBrands($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Collection
    {
        return $this->all($columns, $orderBy, $sortBy);
    }

    /**
     * @return Collection
     */
    public function listProducts(): Collection
    {
        return $this->model->products()->get();
    }

    /**
     * @param Product $product
     */
    public function saveProduct(Product $product)
    {
        $this->model->products()->save($product);
    }

    /**
     * Dissociate the products
     */
    public function dissociateProducts()
    {
        $this->model->products()->each(function (Product $product) {
            $product->company_id = null;
            $product->save();
        });
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sync the users
     *
     * @param array $params
     */
    public function syncUsers($user_id, array $params)
    {
        $this->model->users()->attach($user_id, $params);

    }

    /**
     * Saves the client and its contacts
     *
     * @param array $data The data
     * @param \App\Models\Company $client The Company
     *
     * @return     Client|\App\Models\Company|null  Company Object
     */
    public function save(array $data, Company $company): ?Company
    {
        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            $data['custom_fields'] = $this->parseCustomFields($data['custom_fields']);
        }

        $company->fill($data);
        $company->save();

        if (isset($data['contacts'])) {
            $contacts = $this->contact_repo->save($data['contacts'], $company);
        }
        return $company;

    }


    /**
     * Store vendors in bulk.
     *
     * @param array $vendor
     * @return vendor|null
     */
    public function create($company): ?Company
    {
        return $this->save($company, CompanyFactory::create(auth()->user()->company()->id, auth()->user()->id));
    }

    private function parseCustomFields($fields): array
    {
        foreach ($fields as &$value) {
            $value = (string)$value;
        }
        return $fields;
    }

}
