@extends('layouts.app')
@section('style')

    <script>

        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';
            $('#user_sample_1').DataTable();
        });

    </script>

@endsection
@section('content')
    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
{{--        <div class="page-header page-header-dark has-cover" style="border: 1px solid #ddd; border-bottom: 0;">--}}
        <div class="page-header page-header-dark has-cover">
            <div class="page-header-content header-elements-inline">
                <div class="page-title">
                    <h5>
                        <i class="icon-arrow-left52 mr-2"></i>
                        <span class="font-weight-semibold">Users</span> - All
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="#" class="breadcrumb-item">Current</a>
                        <span class="breadcrumb-item active">Users</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">

            <!-- Default ordering -->
            <div class="card border-top-teal-400 border-top-3">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">Active Users</h5>
                    @can('user_create')
                    <a href="{{ route('users.create') }}" class="btn bg-teal legitRipple">
                        <i class="icon-plus-circle2"></i>
                        User</a>
                    @endcan
                </div>

                <div class="card-body">
                </div>
                <table class="table table-hover table-striped table-responsive" id="user_sample_1">
                    <thead>
                    <tr>
                        <th>User#</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        @canany(['user_edit','user_enable-disable','user_activity-log'])
                        <th>Action</th>
                        @endcanany
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($users))
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>{{ $user->created_at->format('h:i A') }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <?php $roles = implode($user->roles->pluck('name','name')->all()); ?>
                        <td>{{ empty($roles) ? '---' : ucwords($roles) }}</td>
                        <td>
                            @if($user->is_active == 1)
                            <h5><span class="badge badge-success">Enabled</span></h5>
                                @else
                                <h5><span class="badge badge-danger">Disabled</span></h5>
                            @endif
                        </td>
                        @canany(['user_edit','user_enable-disable','user_activity-log'])
                        <td>
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a href="#" class=list-icons-item" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        @can('user_edit')
                                        <a href="{{ route('users.edit',$user->id) }}" class="dropdown-item"> <i></i>Edit</a>
                                        @endcan
                                        @can('user_enable-disable')
                                        @if($user->is_active == 1)
                                            <a href="{{ route('userStatus',$user->id) }}" class="dropdown-item"><i></i>Disabled </a>
                                        @else
                                            <a href="{{ route('userStatus',$user->id) }}" class="dropdown-item"><i></i>Enabled </a>
                                        @endif
                                        @endcan
                                        @can('user_activity-log')
                                        <a href="{{ route('activityLogs',$user->id) }}" class="dropdown-item"> <i></i>Activity Logs</a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </td>
                        @endcanany
                    </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <!-- /default ordering -->

        </div>
        <!-- /content area -->

@endsection()
