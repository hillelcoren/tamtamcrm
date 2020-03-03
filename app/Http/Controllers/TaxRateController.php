<?php

namespace App\Http\Controllers;

use App\Factory\TaxRateFactory;
use App\Filters\TaxRateFilter;
use App\Repositories\TaxRateRepository;
use App\Repositories\Interfaces\TaxRateRepositoryInterface;
use App\Requests\TaxRate\CreateTaxRateRequest;
use App\Requests\SearchRequest;
use App\Requests\TaxRate\UpdateTaxRateRequest;
use App\Http\Controllers\Controller;
use App\TaxRate;
use App\Transformations\TaxRateTransformable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaxRateController extends Controller
{
    use TaxRateTransformable;

    /**
     * @var TaxRateRepositoryInterface
     */
    private $tax_rate_repo;

    /**
     * TaxRateController constructor.
     * @param TaxRateRepositoryInterface $tax_rate_repo
     */
    public function __construct(TaxRateRepositoryInterface $tax_rate_repo)
    {
        $this->tax_rate_repo = $tax_rate_repo;
    }

    public function index(SearchRequest $request)
    {
        $tax_rates =
            (new TaxRateFilter($this->tax_rate_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($tax_rates);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(CreateTaxRateRequest $request)
    {
        $tax_rate = TaxRateFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id);
        $this->tax_rate_repo->save($request->all(), $tax_rate);

        return response()->json($this->transformTaxRate($tax_rate));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCourierRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateTaxRateRequest $request, $id)
    {
        $taxRate = $this->tax_rate_repo->findTaxRateById($id);
        $taxRate = $this->tax_rate_repo->save($request->all(), $taxRate);
        return response()->json($this->transformTaxRate($taxRate));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function archive(int $id)
    {
        $taxRate = $this->tax_rate_repo->findTaxRateById($id);
        $taxRateRepo = new TaxRateRepository($taxRate);
        $taxRateRepo->delete();
        return response()->json('deleted');
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        $tax_rate = TaxRate::withTrashed()->where('id', '=', $id)->first();
        $this->tax_rate_repo->newDelete($tax_rate);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = TaxRate::withTrashed()->where('id', '=', $id)->first();
        $this->tax_rate_repo->restore($group);
        return response()->json([], 200);
    }

}
