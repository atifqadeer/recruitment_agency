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
                        <span class="font-weight-semibold">IP Address</span> - Add
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="#" class="breadcrumb-item">Message</a>
                        <span class="breadcrumb-item active">Number</span>
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
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                            @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                            {{ session('success') }}
                            </div>
                            @endif
                            @if (session('error'))

                            <div class="alert alert-danger alert-dismissible">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                            {{ session('error') }}
                            </div>
                            @endif
                                <div class="col-md-10 offset-md-1">
                                    <div class="header-elements-inline">
                                        <h5 class="card-title">Add an IP Address</h5>
                                        <a href="{{ route('ip-addresses.index') }}" class="btn bg-slate-800 legitRipple">
                                            <i class="icon-cross"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                <form action="{{ route('msg.applicant.number') }}" method="POST" id="usrform">
                                @csrf
                                <div class="form-group">
                                    <input type="text" name="app_number" maxlength="11" id="app_number" number class="form-control allow-numeric" placeholder="Entere Applicant Number Here..." required>
                                    <textarea name="app_message" id="app_message" class="form-control" placeholder="Enter Message Here..." required rows=6, cols=40 ></textarea>
                                    <!-- <textarea name="app_message" id="app_message" class="form-control" placeholder="Enter Message Here..." required rows=2, cols=20 ></textarea> -->
                                    </div>
                                    <div class="text-right">
                                    <button value="submit" class="btn bg-teal legitRipple">Save<i class="icon-paperplane ml-2"></i></button>
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
        <!-- /content area -->
<script>
  $(document).ready(function() {
      $(".allow-numeric").on("keypress keyup blur",function (event) {    
           $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                $(".error").css("display", "inline");
                event.preventDefault();
            }else{
            	$(".error").css("display", "none");
            }
        });
    });
</script>
@endsection
