<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return mixed
     */
    protected function authenticated($request, $user)
    {
        // Check if the user has the 'can-see' permission
        if ($user->cannot('can-see-all')) {
            // Redirect to the 'content.shared' route for users with 'can-see' permission
            return redirect()->route('shared');
        }

        // Check if the user has the 'can-see-all' permission
        if ($user->can('can-see-all')) {
            // Redirect to the 'list.contents' route for users with 'can-see-all' permission
            return redirect()->route('list.contents');
        }

        // Abort for users who don't have the required permissions
        abort(403, 'Page not found.');
    }
}
