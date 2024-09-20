<?php

namespace Horsefly\Http\Controllers\Administrator;

use Illuminate\Http\Request;
use Horsefly\Http\Controllers\Controller;
use Horsefly\LoginDetail;
use Spatie\Permission\Models\Role;

use Auth;
use DB;

class LoginDetailControlle extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if($user->name = 'Super Admin' && $user->is_admin = 1)
        {
            $users_data = LoginDetail::join('users', 'login_details.user_id', '=', 'users.id' )
            ->select('login_details.id as login_id','login_details.login_date','login_details.user_id as userId','login_details.created_at','users.name','users.email','users.id',DB::raw('MAX(login_details.login_time) as max_time'))
            ->orderBy('login_details.created_at', 'desc')
            ->groupBy(['login_details.login_date','userId'])
            ->get();
            // echo '<pre>'; print_r($users_data);echo '</pre>';exit();
            $sr_no = 1;
            return view('administrator.login_details.login_details', compact('users_data','sr_no'));
        }
        else
        {
            return Redirect::to(url()->previous());
        }
       

    }
    public function showUserLoginDetails($id)
    {
        $user = Auth::user();
        if($user->name = 'Super Admin' && $user->is_admin = 1)
        {
            $sr_no = 1;
        $users_data = LoginDetail::join('users', 'login_details.user_id', '=', 'users.id' )
            ->select('login_details.id as login_id','login_details.login_date','login_details.user_id as userId','login_details.created_at','login_details.login_time as att_time','users.name','users.email','users.id')
            ->where('login_details.user_id', $id)
            ->orderBy('login_details.login_date', 'desc')
            ->orderBy('att_time', 'ASC')
            ->get();

            return view('administrator.login_details.user_login_details', compact('users_data','sr_no'));
        }
        else
        {
            return Redirect::to(url()->previous());
        }
    }
}
