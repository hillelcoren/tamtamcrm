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


    public function update(UpdateAccountUserRequest $request, User $user)
    {
        $company = auth()->user()->account_user();

        if (auth()->user()->isAdmin()) {
            $user_array = $request->all();

            if (array_key_exists('account', $user_array)) {
                ;
            }
            unset($user_array['company_user']);

            $user->fill($user_array);
            $user->save();
        }

        $company_user = AccountUser::whereUserId($user->id)->whereAccountId($company->id)->first();

        $company_user->fill($request->input('company_user'));
        $company_user->save();

        return response()->json($company_user->fresh());
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
