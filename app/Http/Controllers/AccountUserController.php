<?php

namespace App\Http\Controllers;

use App\AccountUser;
use App\Http\Requests\AccountUser\UpdateAccountUserRequest;
use App\User;
use Illuminate\Http\Request;

class CompanyUserController extends Controller
{

    public function __construct()
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
// return view('signup.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
//
    }


    public function store(CreateAccountRequest $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
//
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
//
    }

    public function update(UpdateCompanyUserRequest $request, User $user)
    {
        $account = auth()->user()->company();


        $account_user = AccountUser::whereUserId($user->id)->whereAccountId($account->id)->first();

        if (!$company_user) {
            throw new ModelNotFoundException("Company User record not found");
            return;
        }

        if (auth()->user()->isAdmin()) {
            $account_user->fill($request->input('account_user'));
        } else {
            $account_user->fill($request->input('account_user')['settings']);
            $account_user->fill($request->input('account_user')['notifications']);
        }

        $account_user->save();

        return response()->json($account_user->fresh());
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
//
    }
}
