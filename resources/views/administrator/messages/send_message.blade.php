@extends('layouts.app')

@section('content')
    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
{{--        <div class="page-header page-header-dark has-cover" style="border: 1px solid #ddd; border-bottom: 0;">--}}
        <div class="page-header page-header-dark has-cover">
            <div class="page-header-content header-elements-inline">
                <div class="page-title">
                    <h5>
                        <a href="#"><i class="icon-arrow-left52 mr-2" style="color: white;"></i></a>
                        <span class="font-weight-semibold">Send Message</span>
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="#" class="breadcrumb-item">User Messages</a>
                        <a href="#" class="breadcrumb-item">Current</a>
                        <span class="breadcrumb-item active">Send Message</span>
                    </div>
                </div>
				@canany(['applicant_chat-box'])
                <div class="nav-item avatar dropdown" style="margin-right: 25px !important;">
                    <a class="nav-link dropdown-toggle waves-effect waves-light" id="navbarDropdownMenuLink-5" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <?php $count=0; ?>
                            @foreach($user_info as $info)
                                <?php
                                   $count+=$info->total;
                                ?>
                                
                            @endforeach
                        <span class="badge badge-danger ml-2 total_notifications_new" id="total_notify_count" >{{$count}}</span>
                        <i class="fas fa-bell"></i>
                    </a>
                    <ul class="dropdown-menu divScroll" id="notification_list">
                        <li class="head text-light bg-dark">
                        <div class="row">
                        <div class="col-lg-12 col-sm-12 col-12">
                        <span>Notifications <span class="total_notifications_new">{{$count}}</span></span>
                        <a href="{{route('mark_msg_as_read')}}" class="float-right text-light">Mark all as read</a>
                        </div>
                        </li>
                    <!-- <div class="dropdown-menu dropdown-menu-lg-right dropdown-secondary" aria-labelledby="navbarDropdownMenuLink-5"> -->
                        @foreach($user_info as $info)
                        <?php 
                        // $applicant_notify_name = substr(str_replace(array('{','}','"'),'',$info->applicant_name),5); 
                        $applicant_notify_name = $info->applicant_name.' ('.$info->applicant_postcode.')'; 
                        ?>
                            <a href="#" id="{{$applicant_notify_name}}" data-id="{{$info->applicant_id}}" class="notify_click" >
                            <li class="notification-box">
                                <div class="row">
                                <div class="col-lg-3 col-sm-3 col-3 text-center">
                                <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="w-50 rounded-circle">
                                </div>
                                <div class="col-lg-8 col-sm-8 col-8">
                                <strong class="text-info">{{$applicant_notify_name}}</strong>
                                <span class="badge badge-danger ml-2 applicant_notifications" style="float: right;">{{$info->total}}</span>
                                <div>
                                {{ str_limit($info->message, $limit = 75, $end = '...') }}
                                    <!-- {{substr($info->message, 0, 70).'...'}} -->
                                </div>
                                <small class="text-warning">{{$info->created_at}}</small>
                                </div>
                                </div>
                            </li>
                            </a>
                            <hr>
                        @endforeach
                    </ul>
                    <!-- </div> -->
                </div>
                @endcanany
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">
            <div class="row">
                <div class="col-md-3">

                </div>
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    <div class="header-elements-inline">
                                        <h5 class="card-title">Send Message To Applicants</h5>
                                        <!-- <a href="{{ route('applicants.index') }}" class="btn bg-slate-800 legitRipple">
                                            <i class="icon-cross"></i> Cancel
                                        </a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    <form id="form_send_message" action="#">
                                 @csrf
                                    <div class="form-group">
                                        <label>Enter Applicant Numbers:</label>
                                        </div>
                                    <div class="form-group border border-light rounded">
                                            <textarea  id="applicant_numbers" cols="20"
                                                       rows="3" class="form-control"
                                                       placeholder="Please enter comma seperated applicant numbers like 07500000000,07500000000"></textarea>
                                        </div>
                                        <div class="form-group">
                                        <label>Message:</label>
                                        </div>
                                        <div class="form-group border border-light rounded">
                                            <textarea  id="applicant_message" cols="40"
                                                       rows="10" required class="form-control"
                                                       placeholder="Write a message..."></textarea>
                                        </div>
                                    <div class="text-right">
                                        <a href="javascript:;" class="btn bg-teal legitRipple" id="send_message_app" ><i class="icon-paperplane"></i> Send</a>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /form centered -->
        </div>
		        @include('layouts.small_chat_box')

        <!-- /content area -->
@section('script')

@endsection 
@endsection
