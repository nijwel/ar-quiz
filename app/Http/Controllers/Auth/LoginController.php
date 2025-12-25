<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware( 'guest' )->except( 'logout' );
        $this->middleware( 'auth' )->only( 'logout' );
    }

    /**
     * Create a new controller instance.
     *
     * @return RedirectResponse
     */
    public function login( Request $request ): RedirectResponse {
        $this->validate( $request, [
            'login'    => 'required',
            'password' => 'required',
        ] );

        $loginInput = $request->login;
        $password   = $request->password;
        $type       = $request->login_type;

        if ( $type === 'email' ) {
            if ( auth()->attempt( ['email' => $loginInput, 'password' => $password] ) ) {
                return auth()->user()->type === 'admin'
                ? redirect()->route( 'admin.home' )
                : redirect()->route( 'home' );
            }
        } else {
            if ( auth()->attempt( ['student_id' => $loginInput, 'password' => $password, 'type' => 'user'] ) ) {
                return redirect()->route( 'home' );
            }
        }

        return redirect()->route( 'login' )
            ->with( 'error', 'আপনার দেওয়া তথ্যগুলো সঠিক নয়।' )
            ->withInput();
    }

}
