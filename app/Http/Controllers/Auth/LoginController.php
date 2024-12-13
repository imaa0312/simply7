<?php

namespace App\Http\Controllers\Auth;

use DB;
use App\Http\Controllers\Controller;
use App\Models\MKecamatanModel;
use App\Models\MKelurahanDesaModel;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Response;
use Auth;
use App\Models\MRoleModel;
use Gloudemans\Shoppingcart\Facades\Cart;

class LoginController extends Controller
{
    
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

    use AuthenticatesUsers {
        attemptLogin as attemptLoginAtAuthenticatesUsers;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */

    public function showLoginForm()
    {
        return view('adminlte::auth.login-withoutvue');
    }

    public function loginForm()
    {
        session(['link' => url()->previous()]);
        return view('frontend.login');
    }
    
    public function registerForm()
    {
        $provinsi = MProvinsiModel::orderBy('id','DESC')->get();
        return view('frontend.register', compact('provinsi'));
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function authenticated(Request $request, $user)
    {
        if( $user->status == 'inactive'){
            Auth::logout();

            return redirect('/masuk')->with('error_message','Silahkan vertifikasi email terlebih dahulu');

        }
        $roleSales = MRoleModel::where('name', 'Admin')->first();
        $roleCustomer = MRoleModel::where('name', 'Customer')->first();

        if( $user->role != $roleCustomer->id ){
            return redirect('/home');
        }else if($user->role == $roleCustomer->id){
            Cart::restore(Auth::id());
            $daftar     = "https://".$_SERVER['SERVER_NAME']."/daftar";
            $daftar1    = "localhost:8000/daftar";
            $daftar2    = "localhost/erp-tum/public/daftar";
            if(session('link') == $daftar || session('link') == $daftar1 || session('link') == $daftar2){
                return redirect('/')->with('success_message','Login Berhasil');
            }else{
                return redirect(session('link'))->with('success_message','Login Berhasil');
            }
        }
    }

    /**
     * Returns field name to use at login.
     *
     * @return string
     */
    public function username()
    {
        return config('auth.providers.users.field','email');
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        if ($this->username() === 'email') return $this->attemptLoginAtAuthenticatesUsers($request);
        if ( ! $this->attemptLoginAtAuthenticatesUsers($request)) {
            return $this->attempLoginUsingUsernameAsAnEmail($request);
        }
        return false;
    }

    /**
     * Attempt to log the user into application using username as an email.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attempLoginUsingUsernameAsAnEmail(Request $request)
    {
        return $this->guard()->attempt(
            ['email' => $request->input('username'), 'password' => $request->input('password')],
            $request->has('remember'));
    }

    protected function credentials(Request $request)
    {
        $field = filter_var($request->get($this->username()), FILTER_VALIDATE_EMAIL)
            ? $this->username()
            : 'username';

        return [
            $field => $request->get($this->username()),
            'password' => $request->password,
        ];
    }

    public function logout(Request $request)
    {
        if( auth()->user()->role == 9){
            if(Cart::count() > 0){
                Cart::store(Auth::id());
            }
        }
        $this->guard()->logout();
        $request->session()->invalidate();
        return redirect('/');
    }

}
