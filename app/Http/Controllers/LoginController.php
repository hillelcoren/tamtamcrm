<?php

namespace App\Http\Controllers;

use App\AccountUser;
use App\CompanyToken;
use App\Requests\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User;
use JWTAuth;
use JWTAuthException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    private function getToken($email, $password)
    {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                    'token' => $token
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    public function doLogin(LoginRequest $request)
    {
        $this->forced_includes = ['company_users'];

        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return response()->json(['message' => 'Too many login attempts, you are being throttled'], 401)
                             ->header('X-App-Version', config('ninja.app_version'))
                             ->header('X-Api-Version', config('ninja.api_version'));
        }

        if ($token = auth()->attempt($request->all())) {
            $token = $this->getToken($request->email, $request->password);
            $user = auth()->user();
            $user->auth_token = $token;
            $user->save();

            $default_account = $user->accounts->first()->domains->default_company;
            $user->setAccount($default_account);

            $accounts = AccountUser::whereUserId($user->id)->with('account')->get();

            CompanyToken::updateOrCreate(['user_id' => $user->id], [
                'token' => $token,
                'user_id' => $user->id,
                'account_id' => $default_account->id,
                'domain_id' => $user->accounts->first()->domains->id
            ]);

            $response = [
                'success' => true,
                'data' => [
                    'account_id' => $default_account->id,
                    'id' => $user->id,
                    'auth_token' => $user->auth_token,
                    'name' => $user->name,
                    'email' => $user->email,
                    'accounts' => $accounts
                ]
            ];

            return response()->json($response, 201);
        }

        return response()->json(['success' => false, 'data' => 'Record doesnt exists']);

    }

    public function showLogin()
    {
        // show the form
        return View::make('login');
    }

    public function doLogout()
    {
        Auth::logout(); // log the user out of our application
        return Redirect::to('login'); // redirect the user to the login screen
    }

}
