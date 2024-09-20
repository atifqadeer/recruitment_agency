<?php

namespace Horsefly\Http\Controllers\Administrator;

use DateTime;
use Horsefly\Audit;
//use Horsefly\Observers\UserObserver;
use Horsefly\User;
use Illuminate\Http\Request;
use Horsefly\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
//        $this->middleware('is_admin');

        $this->middleware('permission:user_list|user_create|user_edit|user_enable-disable|user_activity-log', ['only' => ['index']]);
        $this->middleware('permission:user_create', ['only' => ['create','store']]);
        $this->middleware('permission:user_edit', ['only' => ['edit','update']]);
        $this->middleware('permission:user_enable-disable', ['only' => ['getUserStatusChange']]);
        $this->middleware('permission:user_activity-log', ['only' => ['activityLogs','userLogs']]);
        $this->middleware('permission:role_assign-role', ['only' => ['assignRoleToUsers']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::where(["is_admin" => 0])->get();
//        $users = User::all();
        return view('administrator.users.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name','name')->all();

        return view('administrator.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        date_default_timezone_set('Europe/London');
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:15',
            'roles' => 'required'
        ])->validate();

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->save();

        $user->assignRole($request->input('roles'));

        $last_inserted_user = $user->id;
        if ($last_inserted_user) {
            DB::table("users")->where('id', $last_inserted_user)->update(['is_admin' => 0]);
            return redirect('users')->with('user_success_msg', 'User Added Successfully');
        } else {
            return redirect('users.create')->with('user_add_error', 'WHOOPS! User Could not Added');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        return view('administrator.users.edit', compact('user','roles','userRole'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        date_default_timezone_set('Europe/London');

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'roles' => 'required'
        ]);

        $update_data  = [ "name" => $request->Input('name'), "email" => $request->Input('email') ];
        if ($request->filled('password'))
            $update_data['password'] = bcrypt($request->Input('password'));
//        User::where(["id" => $id])->update($update_data);
        $user = User::find($id);
        $user->update($update_data);

        DB::table('model_has_roles')->where('model_id',$id)->delete();
        $user->assignRole($request->input('roles'));

        return redirect('users')->with('updateUserSuccessMsg', 'User has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    public function assignRoleToUsers(Request $request)
    {
        // print_r($request->input('role'));exit();
        foreach ($request->input('users') as $user_id) {
            DB::table('model_has_roles')->where('model_id',$user_id)->delete();
            $user = User::find($user_id);
         @$user->assignRole($request->input('role'));
        }
        return redirect()->back()->with('success','Role assigned successfully');
    }

    public function getUserStatusChange($id)
    {
        $user = User::find($id);
//        $user_observer = new UserObserver();
        if ($user->is_active == 1) {
            if (DB::table('users')->where('id', $id)->update(['is_active' => 0])) {
//                $user_observer->updated($user, "Status: Disabled for {$user->name} successfully", ["is active" => "Disabled"]);
                return redirect('users')->with('UserDisableSuccessMsg', 'User has been disabled Successfully');
            } else {
                return redirect('users')->with('UserDisableErrMsg', 'WHOOPS! Something Went Wrong!!');
            }

        } elseif ($user->is_active == 0) {
            if (DB::table('users')->where('id', $id)->update(['is_active' => 1])) {
//                $user_observer->updated($user, "Status: Enabled for {$user->name} successfully", ["is active" => "Enabled"]);
                return redirect('users')->with('UserEnableSuccessMsg', 'User has been enabled Successfully');
            } else {
                return redirect('users')->with('UserEnableErrMsg', 'WHOOPS! Something Went Wrong!!');
            }
        }
    }

    /**
     * Display activity log view
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activityLogs($id)
    {
        $user = User::find($id);

        return view('administrator.users.activity_logs',
            compact('user'));
    }

    /**
     * Ajax request for data table to fetch user activity logs
     *
     * @param $id
     * @return mixed
     */
    public function userLogs($id)
    {
//        $start_date = date_format(new DateTime(), 'Y-m-d 00:00:00.00');
//        $end_date = date_format(new DateTime(), 'Y-m-d 23:59:59.99');
//        $audits = Audit::where('user_id', $id)->where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date)->orderBy('id', 'DESC')->get();
        $audits = Audit::where('user_id', $id)->orderBy('id', 'DESC')->get();

        /*** Data table column for date
        ->addColumn("created_date",function($audit){
            $created_at = new DateTime($audit->audit_added_date);
            $date = date_format($created_at,'d F Y');
            return $date;
        })
         */
        return datatables($audits)
            ->addColumn('user', function ($audit) {
                return $audit->user_id;
            })
                         ->addColumn('details', function ($audit) {
                $content = "";
                $content .= '<a href="#" class=""
                                 data-controls-modal="#modal_audit_details'.$audit->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#modal_audit_details'.$audit->id.'">
                                 Details</a>';
                $content .= '<div id="modal_audit_details'.$audit->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Action Details</h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" style="max-height: 500px; overflow-y: auto;">';
                if (!empty($audit->data['changes_made'])) {
                    $content .= '<h6 class="font-weight-semibold">Changes</h6>';
                    foreach ($audit->data['changes_made'] as $key_2 => $val_2) {
                        $content .= '<div class="col-1"></div>';

                        if (is_array($val_2)) {
                            $content .= '<p><span class="font-weight-semibold">'.str_replace('_', ' ', $key_2).': </span>'.implode(', ', $val_2).'</p>';
                        } else {
                            $content .= '<p><span class="font-weight-semibold">'.str_replace('_', ' ', $key_2).': </span>'.$val_2.'</p>';
                        }
                    }


//                    foreach ($audit->data['changes_made'] as $key_2 => $val_2) {
//                        $content .= '<div class="col-1"></div>';
//                        $content .= '<p><span class="font-weight-semibold">'.str_replace('_', ' ', $key_2).': </span>'.$val_2.'</p>';
//                    }
                } else {
                    $content .= '<h6 class="font-weight-semibold">Details</h6>';
                    foreach ($audit->data as $key_1 => $val_1) {
                        $content .= '<div class="col-1"></div>';

                        if (is_array($val_1)) {
                            $content .= '<p><span class="font-weight-semibold">'.str_replace('_', ' ', $key_1).': </span>'.implode(', ', $val_1).'</p>';
                        } else {
                            $content .= '<p><span class="font-weight-semibold">'.str_replace('_', ' ', $key_1).': </span>'.$val_1.'</p>';
                        }
                    }



//                    foreach ($audit->data as $key_1 => $val_1) {
//                        $content .= '<div class="col-1"></div>';
//                        if (in_array($key_1, [
//                            'id', 'is_admin', 'is_active', 'email_verified_at', 'updated_at', 'changes_made',
//                            'applicant_cv', 'applicant_user_id'
//                        ])) {
//                            continue;
//                        } else {
//                            $content .= '<p><span class="font-weight-semibold">'.str_replace('_', ' ', $key_1).': </span>'.$val_1.'</p>';
//                        }
//                    }
                }
                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;
            })


            ->addColumn('module', function ($audit) {
				
                $module = explode("\\",$audit->auditable_type);
				//dd($module[count($module) - 1]);
                return $module[count($module) - 1];
            })
            ->rawColumns(['user', 'details', 'module'])
            ->make(true);
    }
}
