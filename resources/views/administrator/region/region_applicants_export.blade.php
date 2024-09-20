@extends('layouts.app')
@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    
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
                        <span class="font-weight-semibold">Applicants</span>
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="{{ route('users.index') }}" class="breadcrumb-item">Applicants</a>
                        <span class="breadcrumb-item active">Export</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">
            <div class="row">
                <div class="col-md-3">

                </div>
                <div class="col-md-5">
                    <div class="card border-top-teal-400 border-top-3">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    <div class="header-elements-inline">
                                        <h5 class="card-title">Export Applicants</h5>
                                        <a href="{{ route('users.index') }}" class="btn bg-slate-800 legitRipple">
                                            <i class="icon-cross"></i> Cancel
                                        </a>
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
                                    {{ Form::open(array('route' => 'region.applicants.export','method' => 'POST' )) }}
                                    <div class="list-icons">
                                        <b>From: </b>
                                        <div id="user_stats_start_date" class="input-append">
                                        <span class="add-on">
                                          <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                        </span>&ensp;
                                            <input data-format="dd-MM-yyyy" type="text" name="start_date" required>
                                        </div>
                                        <b>To: </b>
                                        <div id="user_stats_end_date" class="input-append">
                                        <span class="add-on">
                                          <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                        </span>&ensp;
                                            <input data-format="dd-MM-yyyy" type="text" name="end_date" required> 
                                        </div>
                                        <a class="list-icons-item" data-action="reload"></a>
                                    </div>
                                    <div class="form-group">
                                    {{ Form::label('roles','Roles') }}
                                    <select class="form-control form-control-select2" id="selectUser" name="user_selected" required focus>
                                            <option value="" disabled selected>Please select user</option>  
                                            <option value="44">Nurse</option>        
                                            <option value="45">Non-Nurse</option>        
                                        </select>
                                        <input type="hidden" name="region_id" id="region_id" value="{{$id}}"/>
                                        <!-- {{ Form::label('roles','Roles') }}
                                        {!! Form::select('roles[]', ['' => 'Select User Role'],null, array('class' => 'form-control form-control-select2', 'required')) !!} -->
                                    </div>
                                    <div class="text-right">
                                        {{ Form::button('Export <i class="icon-paperplane ml-2"></i>',['type'=>'submit','class'=>'btn bg-teal legitRipple']) }}
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /form centered -->
        </div>
        <!-- /content area -->

@endsection()
@section('js_file')
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <!-- <script src="{{ asset('js/donut_chart.js') }}"></script>s -->
@endsection
@section('script')
<script>
// $(document).on('click', '.user-statistics', function (event) {
//         // $("#user_stats_end_date_value").dialog({ modal: true, title: event.title, width:350});
//         var user_key = $(this).data('user_key');
//         var user_name = $(this).data('user_name');
//         var start_date = $('#user_stats_start_date_value').val();
//         var end_date = $('#user_stats_end_date_value').val();

//         $('#user_name').html(user_name);

//         $.ajax({
//             url: "{{ route('userStatistics') }}",
//             type: "POST",
//             data: {
//                 _token: "{{ csrf_token() }}",
//                 user_key: user_key,
//                 user_name: user_name,
//                 start_date: start_date,
//                 end_date: end_date
//             },
//             success: function(response){
//                 $('#user_stats_details').html(response);
//                 $('#user_s_date').html(start_date);
//                 $('#user_e_date').html(end_date);
//             },
//             error: function(response){
//                 let raw_html = '<p>WHOOPS! Something Went Wrong!!</p>';
//                 $('#user_stats_details').html(raw_html);
//             }
//         });
//     });
</script>
@endsection
