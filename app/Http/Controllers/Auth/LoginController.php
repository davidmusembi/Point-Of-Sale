<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Rules\ReCaptcha;


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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->middleware('guest')->except('logout');
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Change authentication from email to username
     *
     * @return void
     */
    public function username()
    {
        return 'username';
    }

    public function logout()
    {
        $user = auth()->user();
        if ($user && !$this->isSuperadmin($user)) {
            $this->businessUtil->activityLog($user, 'logout');
        }

        request()->session()->flush();
        \Auth::logout();

        return redirect('/login');
    }

    /**
     * The user has been authenticated.
     * Check if the business is active or not.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Superadmin has no business record — skip all tenant checks.
        if ($this->isSuperadmin($user)) {
            return null;
        }

        $this->businessUtil->activityLog($user, 'login', null, [], false, $user->business_id);

        if (!$user->business->is_active) {
            \Auth::logout();

            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.business_inactive')]
                );
        } elseif ($user->status != 'active') {
            \Auth::logout();

            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.user_inactive')]
                );
        } elseif (!$user->allow_login) {
            \Auth::logout();

            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]
                );
        } elseif (($user->user_type == 'user_customer') && !$this->moduleUtil->hasThePermissionInSubscription($user->business_id, 'crm_module')) {
            \Auth::logout();

            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.business_dont_have_crm_subscription')]
                );
        }
    }

    protected function redirectTo()
    {
        $user = \Auth::user();

        if ($this->isSuperadmin($user)) {
            return '/sadmin';
        }

        $path = '/home';
        if (!$user->can('dashboard.data') && $user->can('sell.create')) {
            $path = '/pos/create';
        } elseif ($user->user_type == 'user_customer') {
            $path = 'contact/contact-dashboard';
        }

        // If logging in from central domain, redirect to tenant domain
        $request = request();
        if (in_array($request->getHost(), config('tenancy.central_domains', []))) {
            if ($user->business_id) {
                $tenant = \App\Tenant::find($user->business_id);
                if ($tenant && $tenant->domains->count() > 0) {
                    $domain = $tenant->domains->first()->domain;
                    $scheme = $request->getScheme();
                    return $scheme . '://' . $domain . '/' . ltrim($path, '/');
                }
            }
        }

        return $path;
    }

    private function isSuperadmin($user): bool
    {
        return $user && $user->isSuperadmin();
    }

    public function validateLogin(Request $request)
    {
        if (config('constants.enable_recaptcha')) {
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
                'g-recaptcha-response' => ['required', new ReCaptcha]
            ]);
        } else {
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
            ]);
        }

    }

}
