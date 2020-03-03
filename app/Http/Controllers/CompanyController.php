<?php

namespace App\Http\Controllers;

use App\Company;
use App\DataMapper\CompanySettings;
use App\Factory\CompanyFactory;
use App\Filters\RecurringInvoiceFilter;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Requests\Company\CreateCompanyRequest;
use App\Requests\Company\UpdateCompanyRequest;
use App\Shop\Brands\Exceptions\UpdateBrandErrorException;
use App\Transformations\CompanyTransformable;
use App\Industry;
use App\Filters\CompanyFilter;
use App\Traits\UploadableTrait;
use App\Requests\SearchRequest;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Jobs\Company\CreateCompany;
use Illuminate\View\View;

class CompanyController extends Controller
{

    use CompanyTransformable, UploadableTrait;

    /**
     * @var CompanyRepositoryInterface
     */
    private $company_repo;

    /**
     * CompanyController constructor.
     * @param CompanyRepositoryInterface $company_repo
     */
    public function __construct(CompanyRepositoryInterface $company_repo)
    {
        $this->company_repo = $company_repo;
    }

    /**
     * @return Factory|View
     */
    public function index(SearchRequest $request)
    {
        $brands =
            (new CompanyFilter($this->company_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($brands);
    }

    /**
     * @param CreateBrandRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateCompanyRequest $request)
    {
        $data = $request->except('company_logo');
        $data['settings']['company_logo'] = null;
        $company = $this->company_repo->save($data,
            (new CompanyFactory)->create(auth()->user()->id, auth()->user()->account_user()->account_id));

        if ($request->company_logo !== null) {
            $this->uploadLogo($request->file('company_logo'), auth()->user()->account_user(), $company);
        }

        $company = $this->transformCompany($company);

        return response()->json($company);
    }

    public function show(int $id)
    {
        $brand = $this->company_repo->findBrandById($id);
        return response()->json($this->transformCompany($brand));
    }

    /**
     * @param UpdateBrandRequest $request
     * @param $id
     *
     * @return RedirectResponse
     * @throws UpdateBrandErrorException
     */
    public function update(UpdateCompanyRequest $request, $id)
    {
        $company = $this->company_repo->findBrandById($id);

        if ($this->entityIsDeleted($company)) {
            return $this->disallowUpdate();
        }

        $this->company_repo->save($request->all(), $company);

        if ($request->company_logo !== null) {
            $this->uploadLogo($request->file('company_logo'), auth()->user()->account_user(), $company);
        }

        return response()->json($this->transformCompany($company));
    }


    public function getIndustries()
    {
        $industries = Industry::all();
        return response()->json($industries);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = Company::withTrashed()->where('id', '=', $id)->first();
        $this->company_repo->restore($group);
        return response()->json([], 200);
    }

    /**
     * @param $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function archive(int $id)
    {
        $brand = $this->company_repo->findBrandById($id);
        $brandRepo = new CompanyRepository($brand);
        //$brandRepo->dissociateProducts();
        $brandRepo->deleteBrand();
    }

    public function destroy(int $id)
    {
        $company = Company::withTrashed()->where('id', '=', $id)->first();
        $this->company_repo->newDelete($company);
        return response()->json([], 200);
    }


}
