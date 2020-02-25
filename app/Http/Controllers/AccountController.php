<?php

namespace App\Http\Controllers;

use App\CompanyToken;
use App\DataMapper\DefaultSettings;
use App\Jobs\Domain\CreateDomain;
use App\Requests\Account\DestroyAccountRequest;
use App\Requests\Account\ShowAccountRequest;
use App\Requests\Account\StoreAccountRequest;
use App\Jobs\Company\CreateCompany;
use App\Jobs\Company\CreateCompanyToken;
use App\Jobs\RegisterNewAccount;
use App\Account;
use App\AccountUser;
use App\Repositories\AccountRepository;
use App\Transformations\AccountTransformable;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Requests\Account\UpdateAccountRequest;
use App\Traits\UploadableTrait;

/**
 * Class AccountController
 * @package App\Http\Controllers
 */
class AccountController extends Controller
{
    use DispatchesJobs, AccountTransformable, UploadableTrait;
    protected $account_repo;
    public $forced_includes = [];

    /**
     * AccountController constructor.
     */
    public function __construct(AccountRepository $account_repo)
    {

        $this->account_repo = $account_repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $accounts = Account::all();
        return response()->json($accounts);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreAccountRequest $request
     * @return mixed
     */
    public function store(StoreAccountRequest $request)
    {
        $this->forced_includes = ['account_user'];
        $account = CreateCompany::dispatchNow($request->all(),
            auth()->user()->accounts->first()->domains->default_company->id);
        $account = $this->account_repo->save($request->all(), $account);
        $account->saveSettings($request->input('settings'), $account);
        $this->uploadLogo($request->file('company_logo'), $account, $account);
        auth()->user()->accounts()->attach($account->id, [
            'is_owner' => 1,
            'is_admin' => 1,
            'is_locked' => 0,
            'permissions' => '',
            //'settings' => DefaultSettings::userSettings(),
        ]);

        /*
     * Required dependencies
     */
        auth()->user()->setAccount($account);

        return response()->json($this->transformAccount($account));
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $account = $this->account_repo->findAccountById($id);
        return response()->json($this->transformAccount($account));
    }

    /**
     *
     *
     * the specified resource in storage.
     * @param UpdateAccountRequest $request
     * @param int $id
     * @return mixed
     */
    public function update(UpdateAccountRequest $request, int $id)
    {
        $account = $this->account_repo->findAccountById($id);
        $account = $this->account_repo->save($request->all(), $account);
        $account->saveSettings($request->input('settings'), $account);
        $this->uploadLogo($request->file('company_logo'), $account, $account);
        return response()->json($this->transformAccount($account));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function destroy(int $id)
    {
        $account = $this->account_repo->findAccountById($id);
        $account->delete();
        return response()->json([], 200);
    }

    public function getCustomFields($entity)
    {

        $account = $this->account_repo->findAccountById(auth()->user()->account_user()->account_id);

        if (empty($account->custom_fields) || empty($account->custom_fields->{$entity})) {
            return response()->json([]);
        }

        $fields = json_decode(json_encode($account->custom_fields), true);

        $custom_fields['fields'][0] = $fields[$entity];

        return response()->json($custom_fields);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function saveCustomFields(Request $request)
    {
        $objAccount = $this->account_repo->findAccountById(auth()->user()->account_user()->account_id);
        $response = $objAccount->update(['custom_fields' => json_decode($request->fields, true)]);
        return response()->json($response);
    }

    public function getAllCustomFields()
    {
        $objAccount = $this->account_repo->findAccountById(auth()->user()->account_user()->account_id);
        return response()->json($objAccount->custom_fields);
    }

    public function changeAccount(Request $request)
    {

        $user = auth()->user();
        CompanyToken::where('token', $user->auth_token)->update(['account_id' => $request->account_id]);
    }
}
