<?php

namespace App\Http\Controllers;

use App\Events\Lead\LeadWasCreated;
use App\Factory\LeadFactory;
use App\Filters\LeadFilter;
use App\Lead;
use App\Repositories\LeadRepository;
use App\Requests\SearchRequest;
use App\Transformations\LeadTransformable;
use Illuminate\Http\Request;
use App\Requests\CreateMessageRequest;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Transformations\MessageUserTransformable;
use App\Transformations\MessageTransformable;
use App\Customer;
use App\Message;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    use LeadTransformable;

    /**
     * @var CustomerRepositoryInterface
     */
    private $lead_repo;

    /**
     * MessageController constructor.
     * @param MessageRepositoryInterface $messageRepository
     * CustomerRepositoryInterface $customerRepository
     * UserRepositoryInterface $userRepository
     */
    public function __construct(LeadRepository $lead_repo)
    {
        $this->lead_repo = $lead_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $leads = (new LeadFilter($this->lead_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($leads);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $user = isset(auth()->user()->id) ? auth()->user()->id : 9874;
        $account_id = isset(auth()->user()->id) ? auth()->user()->account_user()->account_id : 1;
        $lead = $this->lead_repo->save(LeadFactory::create($account_id, $user), $request->all());
        event(new LeadWasCreated($lead, $lead->account));
        return response()->json($this->transformLead($lead));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return mixed
     */
    public function update(int $id, Request $request)
    {
        $lead = $this->lead_repo->findLeadById($id);
        $lead = $this->lead_repo->save($lead, $request->all());
        return response()->json($lead);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function convert(int $id)
    {
        $lead = $this->lead_repo->findLeadById($id);
        $lead = $lead->service()->convertLead();
        return response()->json($lead);
    }

    public function archive(int $id)
    {
        $lead = $this->lead_repo->findLeadById($id);
        $lead->delete();
    }

    public function destroy(int $id)
    {
        $lead = $this->lead_repo->findLeadById($id);
        $this->lead_repo->newDelete($lead);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $lead = Lead::withTrashed()->where('id', '=', $id)->first();
        $this->lead_repo->restore($lead);
        return response()->json([], 200);
    }
}
