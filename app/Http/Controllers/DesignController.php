<?php

namespace App\Http\Controllers;

use App\Design;
use App\Factory\DesignFactory;
use App\Filters\DesignFilter;
use App\Repositories\DesignRepository;
use App\Requests\Design\StoreDesignRequest;
use App\Requests\Design\UpdateDesignRequest;
use App\Requests\SearchRequest;
use App\Traits\CheckEntityStatus;
use App\Transformations\DesignTransformable;

/**
 * Class DesignController
 * @package App\Http\Controllers
 * @covers App\Http\Controllers\DesignController
 */
class DesignController extends Controller
{
    use CheckEntityStatus;
    use DesignTransformable;

    protected $design_repo;

    /**
     * DesignController constructor.
     * @param DesignRepository $design_repo
     */
    public function __construct(DesignRepository $design_repo)
    {
        $this->design_repo = $design_repo;
    }


    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $designs = (new DesignFilter($this->design_repo))->filter($request, auth()->user()->account_user()->account_id);

        return response()->json($designs);
    }


    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $design = $this->design_repo->findDesignById($id);
        return response()->json($design);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function edit(int $id)
    {
        $design = $this->design_repo->findDesignById($id);
        return response()->json($design);
    }

    /**
     * @param int $id
     * @param UpdateDesignRequest $request
     * @return mixed
     */
    public function update(int $id, UpdateDesignRequest $request)
    {
        $design = $this->design_repo->findDesignById($id);
        if ($this->entityIsDeleted($design)) {
            //return $request->disallowUpdate();
        }

        $design->fill($request->all());
        $design->save();

        return response()->json($this->transformDesign($design->fresh()));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function create(int $id)
    {
        $design = DesignFactory::create(auth()->user()->company()->id, auth()->user()->id);

        return response()->json($design);
    }

    /**
     * @param StoreDesignRequest $request
     * @return mixed
     */
    public function store(StoreDesignRequest $request)
    {
        $design = DesignFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id);

        $design->fill($request->all());
        $design->save();

        return response()->json($this->transformDesign($design->fresh()));
    }


    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function destroy(int $id)
    {
        $design = Design::withTrashed()->where('id', '=', $id)->first();
        $this->design_repo->newDelete($design);
        return response()->json([], 200);
    }


    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $designs = Design::withTrashed()->find($ids);

        $designs->each(function ($design, $key) use ($action) {
            if (auth()->user()->can('edit', $design)) {
                //$this->design_repo->{$action}($design);@todo
            }
        });

        return response()->json(Design::withTrashed()->whereIn('id', $ids));
    }


}
