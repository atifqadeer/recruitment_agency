@extends('layouts.app')
@section('title', 'Test Page')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <style>
        .overlay{
			display: none;
			position: fixed;
			width: 100%;
			height: 100%;
			top: 0;
			left: 0;
			z-index: 9999;
			background: rgba(255,255,255,0.8) url("assets/img/gif/loader.gif") center no-repeat;
		}
		body.loading{
			overflow: hidden;   
		}
		/* Make spinner image visible when body element has the loading class */
		body.loading .overlay{
			display: block;
		}
		.card-title-gray{
			color: #6c757d;
		}
    </style>
@endsection

@section('content')
    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header page-header-dark has-cover">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Home</span> - Dashboard</h4>
                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

                <div class="header-elements d-none text-center text-md-left mb-3 mb-md-0 px-2">
                    <button type="button" class="btn bg-indigo-400"><i class="fas fa-sync"></i> Refresh </button>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="#" class="breadcrumb-item">Dashboard</a>
                        <span class="breadcrumb-item active">Statistics</span>
                    </div>

                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>
            </div>
        </div>
        <!-- /page header -->

        <!-- Content area -->
        <div class="content">
           <!-- Quick stats widgets -->
            <div class="row px-2">
                <div class="col-md-4 col-xl-3 px-1">
                    <div class="card card-body">
                        <div class="media">
                            <div class="mr-3 align-self-center">
                                <i class="fas fa-users text-teal-400" style="font-size: 48px;"></i>
                            </div>
                            <div class="media-body text-right">
                                <h3 class="font-weight-semibold mb-0" id="no_of_applicants">0</h3>
                                <span class="text-uppercase font-size-sm text-muted">Applicants</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-xl-3 px-1">
                    <div class="card card-body">
                        <div class="media">
                            <div class="mr-3 align-self-center">
                                <i class="fas fa-user-tie text-primary-400" style="font-size: 48px;"></i>
                            </div>
                            <div class="media-body text-right">
                                <h3 class="font-weight-semibold mb-0" id="no_of_units">0</h3>
                                <span class="text-uppercase font-size-sm text-muted">Total Units</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-xl-3 px-1">
                    <div class="card card-body">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="font-weight-semibold mb-0" id="no_of_offices">0</h3>
                                <span class="text-uppercase font-size-sm text-muted">Head Offices</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i style="font-size: 48px;" class="fas fa-building text-blue-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-xl-3 px-1">
                    <div class="card card-body">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="font-weight-semibold mb-0" id="no_of_open_sales">0</h3>
                                <span class="text-uppercase font-size-sm text-muted">Open Sales</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i class="fa fa-briefcase text-orange-800" style="font-size: 48px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource -->
            <div class="row px-2">
                <div class="col-sm-6 col-xl-3 px-1">
                    <div class="card card-body bg-teal-400 has-bg-image">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="mb-0" id="last_7_days">0</h3>
                                <span class="text-uppercase font-size-xs">Last 7 Days</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i class="fas fa-calendar-week opacity-75" style="font-size: 48px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3 px-1">
                    <div class="card card-body bg-primary-400 has-bg-image">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="mb-0" id="last_21_days">0</h3>
                                <span class="text-uppercase font-size-xs">Last 21 Days</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i class="fas fa-calendar-alt opacity-75" style="font-size: 48px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3 px-1">
                    <div class="card card-body bg-blue-400 has-bg-image">
                        <div class="media">
                            <div class="mr-3 align-self-center">
                                <i class="fas fa-calendar-check opacity-75" style="font-size: 48px;"></i>
                            </div>
                            <div class="media-body text-right">
                                <h3 class="mb-0" id="all_applicants">0</h3>
                                <span class="text-uppercase font-size-xs">All exc 31 Days</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3 px-1">
                    <div class="card card-body bg-orange-800 has-bg-image">
                        <div class="media">
                            <div class="mr-3 align-self-center">
                                <i class="fas fa-file-alt opacity-75" style="font-size: 48px;"></i>
                            </div>
                            <div class="media-body text-right">
                                <h3 class="mb-0">Resource</h3>
                                <span class="text-uppercase font-size-xs">Applicants</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /resource -->

            <div class="row px-2">
                <!-- Daily Stats -->
                <div class="col-md-9 px-1">
                    <div class="card">
                        <div class="card-header text-primary header-elements-inline">
                            <h5 class="card-title"><span class="font-weight-semibold">Daily </span></h5>
                            <div class="header-elements">
                                <div class="list-icons">
                                    <div id="daily_date" class="input-append">
                                        <span class="add-on">
                                        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                        </span>&ensp;
                                    <input data-format="dd-MM-yyyy" type="text" value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}"  id="daily_date_value"> 
                                    </div>
                                    <a class="list-icons-item" data-action="reload"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Applicants -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Applicants</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-nurse text-teal-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                                class="applicant_stats_detail_home text-orange-800"
                                                data-user_key="daily" data-user_detail="quality_cleared"
                                                data-user_home="nurse"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <h6 class="font-weight-semibold mb-0" id="no_of_nurses">0
                                        </h6>
                                        <span class="text-muted">Nurses</span>
                                            </a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-user text-secondary" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                                class="applicant_stats_detail_home text-orange-800"
                                                data-user_key="daily" data-user_detail="quality_cleared"
                                                data-user_home="non-nurse"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <h6 class="font-weight-semibold mb-0" id="no_of_non_nurses">0</h6>
                                        <span class="text-muted">Non Nurses</span>
                                        </a>

                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-phone text-primary-400" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="no_of_callbacks">0</h6>
                                        <span class="text-muted">Callbacks</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-slash text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="no_of_not_interested">0 <span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;Not -</span></h6>
                                        <span class="text-muted">Interested</span>
                                    </div>
                                </div>

                            </div>
                        <!-- /applicants -->

                        <!-- monthly update applicants -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Applicants Updated</h6>
                            </div>
                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-nurse text-teal-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                        class="applicant_stats_detail_home text-orange-800"
                                        data-user_key="daily" data-user_detail="quality_cleared"
                                        data-user_home="nurse"
                                        data-user_update="nurse_update"
                                        data-controls-modal="#applicant_deail_stats"
                                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                        data-target="#applicant_deail_stats">
                                            <h6 class="font-weight-semibold mb-0" id="no_of_nurses_daily_update">0
                                            </h6>
                                            <span class="text-muted">Nurses</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-user text-secondary" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                        class="applicant_stats_detail_home text-orange-800"
                                        data-user_key="daily" data-user_detail="quality_cleared"
                                        data-user_home="non-nurse"
                                        data-user_update="nurse_update"
                                        data-controls-modal="#applicant_deail_stats"
                                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                        data-target="#applicant_deail_stats">
                                            <h6 class="font-weight-semibold mb-0" id="no_of_non_nurses_daily_update">0</h6>
                                            <span class="text-muted">Non Nurses</span>
                                        </a>

                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-phone text-primary-400" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="no_of_callbacks_daily_update">0</h6>
                                        <span class="text-muted">Callbacks</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-slash text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="no_of_not_daily_interested">0<span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;Not -</span></h6>
                                        <span class="text-muted">Interested</span>
                                    </div>
                                </div>

                            </div>

                        <!-- /monthly update applicants -->

                        <!-- Sales -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Sales</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-door-open text-orange-800" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#" id="openLink">
                                            <h6 class="font-weight-semibold mb-0" id="daily_open_sales">0
                                            </h6>
                                            <span class="text-muted">Open</span>
                                        </a>

                                        <form action="{{ url('open_sale') }}" method="get" id="openForm">
                                            @csrf
                                            <input type="hidden" name="app_daily_date" value="" id="app_daily_date">
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-door-closed text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#" id="closeLink">
                                        <h6 class="font-weight-semibold mb-0" id="daily_close_sales">0</h6>
                                        <span class="text-muted">Close</span>
                                        </a>
                                        <form action="{{ url('close_sale') }}" method="get" id="closeForm">
                                            @csrf
                                            <input type="hidden" name="close_app_daily_date" value="" id="close_app_daily_date">
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-building text-primary-400" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="daily_psl_offices">0</h6>
                                        <span class="text-muted">PSL Office</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="far fa-building text-blue-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="daily_non_psl_offices">0<span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;NON -</span></h6>
                                        <span class="text-muted">PSL Office</span>
                                    </div>
                                </div>

                            </div>
                        <!-- /sales -->
                        
                        <!-- sales updated -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Sales Updated</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-door-open text-orange-800" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#" id="openLinkUpdate">
                                            <h6 class="font-weight-semibold mb-0" id="daily_open_sales_update">0
                                            </h6>
                                            <span class="text-muted">Open</span>
                                        </a>

                                        <form action="{{ url('open_sale_update') }}" method="post" id="openFormUpdate">
                                            @csrf
                                            <input type="hidden" name="open_daily_date_update" value="" id="open_daily_date_update">
                                        
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-door-open text-orange-800" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#" id="reOpenLinkUpdate">
                                            <h6 class="font-weight-semibold mb-0" id="daily_open_sales_update">0
                                            </h6>
                                            <span class="text-muted">sale Reopen</span>
                                        </a>

                                        <form action="{{ url('re_open_sale') }}" method="post" id="reOpenFormUpdate">
                                            @csrf
                                            <input type="hidden" name="open_daily_date_update" value="" id="open_daily_date_update">
                                        </form>
                                    </div>
                                </div>

                                
                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-door-closed text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#" id="closeLinkUpdate">
                                            <h6 class="font-weight-semibold mb-0" id="daily_close_sales_update">0</h6>
                                            <span class="text-muted">Close</span>
                                        </a>
                                        <form action="{{ url('close_sale_update') }}" method="post" id="closeFormUpdate">
                                            @csrf
                                            <input type="hidden" name="close_daily_date_update" value="" id="close_daily_date_update">
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-building text-primary-400" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="daily_psl_offices_update">0</h6>
                                        <span class="text-muted">PSL Office</span>
                                    </div>
                                </div>

                                {{-- <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="far fa-building text-blue-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="daily_non_psl_offices_update">0 <span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;NON -</span></h6>
                                        <span class="text-muted">PSL Office</span>
                                    </div>
                                </div> --}}

                            </div>
                        <!-- /sales updated -->

                        <!-- Quality -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Quality</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                            <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                <div>
                                    <i class="fas fa-file-alt text-blue-400" style="font-size: 30px;"></i>
                                </div>

                                <div class="ml-3">
                                    <h6 class="font-weight-semibold mb-0" id="daily_cvs">0</h6>
                                    <span class="text-muted">CVs (Sent)</span>
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-center mb-3 mb-md-0">
                                <div>
                                    <i class="fa fa-ban text-danger-400" style="font-size: 30px;"></i>
                                </div>

                                <div class="ml-3">
                                    <h6 class="font-weight-semibold mb-0" id="daily_cvs_rejected">0</h6>
                                    <span class="text-muted">CVs Rejected</span>
                                </div>
                            </div>

                            <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                <div>
                                    <i class="fa fa-clipboard-check text-teal-400" style="font-size: 30px;"></i>
                                </div>

                                <div class="ml-3">
                                    <h6 class="font-weight-semibold mb-0" id="daily_cvs_cleared">0</h6>
                                    <span class="text-muted">CVs &nbsp;Cleared</span>
                                </div>
                            </div>

                            </div>
                        <!-- /quality -->

                        <!-- CRM Pie Chart -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 10px !important;">
                                <h6 class="card-title card-title-gray">Applicants in CRM Stages (<span id="crm_total">0</span>)</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap pb-0">
                                <!-- CRM Pie Chart -->
                                <div class="col-md-5 d-flex align-items-start">
                                    <div class="chart-container text-center" style="width: 250px; height: 250px;"> <!-- has-scroll -->
                                        <div class="d-inline-block" id="google-donut" data-donut_chart_data="0"></div>
                                    </div>
                                </div>
                                <!-- Charts Legends -->
                                <div class="col-md-3 d-flex align-items-start">
                                    <div class='crm-legend ml-2 pl-1'>
                                        <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="quality_cleared"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key" style='background-color: #0b5baf;'></span>Sent CVs (<span id="no_of_crm_sent">0</span>)</p>
                                    </a>
                                        <a href="#"
                                        class="applicant_daily_detail_stats text-orange-800"
                                        data-user_key="daily" data-user_detail="quality_revert"
                                        data-controls-modal="#applicant_deail_stats"
                                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                        data-target="#applicant_deail_stats">
                                            <p><span class="crm-key" style='background-color: #0b5baf;'></span>Quality Revert (<span id="no_of_quality_revert">0</span>)</p>
                                        </a>
                                        
                                        <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_request"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key"  style='background-color: #0d6fd4;'></span>Request (<span id="no_of_crm_requested">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_confirmation"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key"  style='background-color: #1782f1;'></span>Confirmation (<span id="no_of_crm_confirmed">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_prestart_attended"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">    
                                        <p><span class="crm-key"  style='background-color: #2a8cf2;'></span>Attended (<span id="no_of_crm_prestart_attended">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_start_date"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key"  style='background-color: #458cd3;'></span>Start Date (<span id="no_of_crm_date_started">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_invoice"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key"  style='background-color: #73b4f6;'></span>Invoice (<span id="no_of_crm_invoiced">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_paid"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key"  style='background-color: #98c8f9;'></span>Paid (<span id="no_of_crm_paid">0</span>)</p>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-start">
                                    <div class='crm-legend pl-1'>
                                        <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_reject"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key" style='background-color: #0e78e6;'></span>Rejected CV (<span id="no_of_crm_rejected">0</span>)</p>
                                        </a>
                                        
                                            <a href="#"
                                        class="applicant_daily_detail_stats text-orange-800"
                                        data-user_key="daily" data-user_detail="crm_revert"
                                        data-controls-modal="#applicant_deail_stats"
                                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                        data-target="#applicant_deail_stats">
                                            <p><span class="crm-key" style='background-color: #0e78e6;'></span>Crm Revert  (<span id="no_of_crm_revert">0</span>)</p>
                                        </a>
                                    
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_req_reject"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <p><span class="crm-key"  style='background-color: #2a8cf2;'></span>Rejected By Req (<span id="no_of_crm_request_rejected">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_rebook"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">  
                                        <p><span class="crm-key"  style='background-color: #469cf3;'></span>Rebook (<span id="no_of_crm_rebook">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_not_attended"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">      
                                        <p><span class="crm-key"  style='background-color: #5ca9f6;'></span>Not Attended (<span id="no_of_crm_not_attended">0</span>)</p>
                                    </a> 
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_start_date_hold"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">   
                                        <p><span class="crm-key"  style='background-color: #68acf5;'></span>Start Date Hold (<span id="no_of_crm_start_date_held">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_declined"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">    
                                        <p><span class="crm-key"  style='background-color: #81bcf7;'></span>Declined (<span id="no_of_crm_declined">0</span>)</p>
                                    </a>
                                    <a href="#"
                                                class="applicant_daily_detail_stats text-orange-800"
                                                data-user_key="daily" data-user_detail="crm_dispute"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">    
                                        <p><span class="crm-key"  style='background-color: #97c9f8;'></span>Dispute (<span id="no_of_crm_disputed">0</span>)</p>
                                    </a>
                                    </div>
                                </div>
                            </div>
                        <!-- /crm pie chart -->
                    </div>
                </div>
                <!-- /daily stats -->

                 <!-- Weekly Stats -->
                 <div class="col-md-6 px-1">
                    <div class="card">
                        <div class="card-header text-teal-400 header-elements-inline">
                            <h5 class="card-title"><span class="font-weight-semibold">Weekly</span></h5>
                            <div class="header-elements">
                                <div class="list-icons">
                                    <div id="weekly_date" class="input-append">
                                        <span class="add-on">
                                          <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
                                        </span>&ensp;
                                       <input data-format="dd-MM-yyyy" type="text" value="{{ \Carbon\Carbon::now()->startOfWeek()->format('d-m-Y') }} - {{ \Carbon\Carbon::now()->format('d-m-Y') }}" style="padding-left: 10px; border: #2fa360 solid 1px; width: 164px;" id="weekly_date_value">
                                    </div>
                                    <a class="list-icons-item" data-action="reload"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Applicants -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Applicants</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-nurse text-teal-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                                class="applicant_stats_detail_home text-orange-800"
                                                data-user_key="weekly" data-user_detail="quality_cleared"
                                                data-user_home="nurse"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_nurses"></h6>
                                        <span class="text-muted">Nurses</span>
                                            </a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-user text-secondary" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                                class="applicant_stats_detail_home text-orange-800"
                                                data-user_key="weekly" data-user_detail="quality_cleared"
                                                data-user_home="non-nurse"
                                                data-controls-modal="#applicant_deail_stats"
                                                data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                                data-target="#applicant_deail_stats">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_non_nurses">0</h6>
                                        <span class="text-muted">Non Nurses</span>
                                            </a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-phone text-primary-400" style="font-size: 30px;">0</i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_callbacks">0</h6>
                                        <span class="text-muted">Callbacks</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-slash text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_not_interested">
                                            <span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;Not -</span></h6>
                                        <span class="text-muted">Interested</span>
                                    </div>
                                </div>

                            </div>
                        <!-- /applicants -->
						
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Applicants Updated</h6>
                            </div>
                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;border-top:0px solid !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-nurse text-teal-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                        class="applicant_stats_detail_home text-orange-800"
                                        data-user_key="weekly" data-user_detail="quality_cleared"
                                        data-user_home="nurse"
                                        data-user_update="nurse_update"
                                        data-controls-modal="#applicant_deail_stats"
                                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                        data-target="#applicant_deail_stats">
                                            <h6 class="font-weight-semibold mb-0" id="weekly_no_of_nurses_update">0</h6>
                                            <span class="text-muted">Nurses</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-user text-secondary" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <a href="#"
                                        class="applicant_stats_detail_home text-orange-800"
                                        data-user_key="weekly" data-user_detail="quality_cleared"
                                        data-user_home="non-nurse"
                                        data-user_update="nurse_update"
                                        data-controls-modal="#applicant_deail_stats"
                                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                        data-target="#applicant_deail_stats">
                                            <h6 class="font-weight-semibold mb-0" id="weekly_no_of_non_nurses_update">0</h6>
                                            <span class="text-muted">Non Nurses</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-phone text-primary-400" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_callbacks_update">0</h6>
                                        <span class="text-muted">Callbacks</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-user-slash text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_not_interested_update">0<span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;Not -</span></h6>
                                        <span class="text-muted">Interested</span>
                                    </div>
                                </div>

                            </div>


                        <!-- Sales -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Sales</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-door-open text-orange-800" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                    
                                        <a href="#" id="openLinkWeekly" target="_blank">
                                            <h6 class="font-weight-semibold mb-0" id="weekly_no_of_open_sales">0</h6>
                                            <span class="text-muted">Open</span>
                                        </a>
                                        <form action="{{ url('open_sale_weekly') }}" method="get" id="openFormWeekly">
                                            @csrf

                                            <input type="hidden" name="open_start_date" value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}" id="open_start_date">
                                            <input type="hidden" name="open_end_date" value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}" id="open_end_date">
                                        </form>
                                    
                                    
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-door-closed text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                    <a href="#" id="closeLinkWeekly" target="_blank">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_close_sales">0</h6>
                                        <span class="text-muted">Close</span>
                                        </a>
                                        <form action="{{ url('close_sale_weekly') }}" method="get" id="closeFormWeekly">
                                            @csrf
                                            <input type="hidden" name="close_start_date"  value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}" id="close_start_date">
                                            <input type="hidden" name="close_end_date" value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}" id="close_end_date">
                                        </form>
                                    
                                    
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-building text-primary-400" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_psl">0</h6>
                                        <span class="text-muted">PSL Office</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="far fa-building text-blue-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_no_of_nonpsl">0 <span class="text-muted" style="font-size: 13px; font-weight: 400;">&nbsp;NON -</span></h6>
                                        <span class="text-muted">PSL Office</span>
                                    </div>
                                </div>

                            </div>
                        <!-- /sales -->

                        <!-- Quality -->
                            <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 5px !important;">
                                <h6 class="card-title card-title-gray">Quality</h6>
                            </div>

                            <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap" style="padding-bottom: 15px; !important;">

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fas fa-file-alt text-blue-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_quality_cvs">0</h6>
                                        <span class="text-muted">CVs (Sent)</span>
                                    </div>
                                </div>

                                <div class="col-md-4 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-ban text-danger-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_quality_cvs_rejected">0</h6>
                                        <span class="text-muted">CVs Rejected</span>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-center mb-3 mb-md-0">
                                    <div>
                                        <i class="fa fa-clipboard-check text-teal-400" style="font-size: 30px;"></i>
                                    </div>

                                    <div class="ml-3">
                                        <h6 class="font-weight-semibold mb-0" id="weekly_quality_cvs_cleared">0</h6>
                                        <span class="text-muted">CVs &nbsp;Cleared</span>
                                    </div>
                                </div>

                            </div>
                        <!-- /quality -->

                        <!-- CRM Pie Chart -->
                        <div class="card-header header-elements-sm-inline" style="padding-top: 0 !important; padding-bottom: 10px !important;">
                            <h6 class="card-title card-title-gray">Applicants in CRM Stages (<span id="weekly_crm_total">0</span>)</h6>
                        </div>

                        <div class="card-body d-md-flex align-items-md-center justify-content-md-between flex-md-wrap pb-0">
                            <!-- CRM Pie Chart -->
                            <div class="col-md-5 d-flex align-items-start">
                                <div class="chart-container text-center" style="width: 250px; height: 250px;"> <!-- has-scroll -->
                                    {{-- <div class="d-inline-block" id="weekly_google-donut" data-weekly_donut_chart_data="{{ serialize($weekly_crm_data) }}" data-weekly_donut_colors="{{ serialize($donut_colors['weekly']) }}"></div> --}}
                                </div>
                            </div>
                            <!-- Charts Legends -->
                            <div class="col-md-3 d-flex align-items-start">
                                <div class='crm-legend ml-2 pl-1'>
                                <a href="#" class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="quality_cleared"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key" style='background-color: #036d62;'></span>Sent CVs (<span id="weekly_crm_sent">0</span>)</p>
                                </a>
									 <a href="#"
                                       class="applicant_daily_detail_stats text-orange-800"
                                       data-user_key="weekly" data-user_detail="quality_revert"
                                       data-controls-modal="#applicant_deail_stats"
                                       data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                       data-target="#applicant_deail_stats">
                                        <p><span class="crm-key" style='background-color: #036d62;'></span>Quality Revert (<span id="weekly_crm_quality_revert">0</span>)</p>
                                    </a>
									
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_request"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">    
                                    <p><span class="crm-key"  style='background-color: #168d81;'></span>Request (<span id="weekly_crm_requested">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_confirmation"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">    
                                    <p><span class="crm-key"  style='background-color: #28ada2;'></span>Confirmed (<span id="weekly_crm_confirmed">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_prestart_attended"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #38bdaf;'></span>Attended (<span id="weekly_crm_prestart_attended">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_start_date"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #48c3b7;'></span>Start Date (<span id="weekly_crm_date_started">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_invoice"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #5bd1c5;'></span>Invoiced (<span id="weekly_crm_invoiced">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_paid"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #76ddd3;'></span>Paid (<span id="weekly_crm_paid">0</span>)</p>
                                </a>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-start">
                                <div class='crm-legend pl-1'>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_reject"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key" style='background-color: #059c8d;'></span>Rejected CV (<span id="weekly_crm_rejected">0</span>)</p>
                                </a>
									
									 <a href="#"
                                       class="applicant_daily_detail_stats text-orange-800"
                                       data-user_key="weekly" data-user_detail="crm_revert"
                                       data-controls-modal="#applicant_deail_stats"
                                       data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                       data-target="#applicant_deail_stats">
                                        <p><span class="crm-key" style='background-color: #059c8d;'></span>Crm Revert (<span id="weekly_crm_revert">0</span>)</p>
                                    </a>
									
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_req_reject"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #15b4a4;'></span>Rejected By Req (<span id="weekly_crm_request_rejected">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_rebook"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #22c7b6;'></span>Rebook (<span id="weekly_crm_rebook">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_not_attended"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #3ad1c2;'></span>Not Attended (<span id="weekly_crm_not_attended">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_start_date_hold"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">   
                                    <p><span class="crm-key"  style='background-color: #4fd8ca;'></span>Start Date Hold (<span id="weekly_crm_start_date_held">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_declined"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #65e6d9;'></span>Declined (<span id="weekly_crm_declined">0</span>)</p>
                                </a>
                                <a href="#"
                                               class="applicant_daily_detail_stats text-orange-800"
                                               data-user_key="weekly" data-user_detail="crm_dispute"
                                               data-controls-modal="#applicant_deail_stats"
                                               data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                               data-target="#applicant_deail_stats">
                                    <p><span class="crm-key"  style='background-color: #8cf8ed;'></span>Disputed (<span id="weekly_crm_disputed">0</span>)</p>
                                </a>
                                </div>
                            </div>
                        </div>
                        <!-- /crm pie chart -->

                    </div>
                </div>
                <!-- /weekly stats -->

            </div>

            
           
        </div>
        <!-- /content area -->

        <!-- User Statistics Modal -->
        <div id="user_stats" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-orange-800">
                            <span class="font-weight-semibold" id="user_name">[user-name]</span>'s Statistics - <span class="font-size-base">From: <span id="user_s_date"></span> To: <span id="user_e_date"></span></span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span class="text-orange-800">&times;</span></button>
                    </div>
                    <div class="modal-body" id="user_stats_details" style="max-height: 500px; overflow-y: auto; padding-bottom: 0 !important;">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-orange-800 legitRipple" data-dismiss="modal">CLOSE
                        </button>
                    </div>

                </div>
            </div>
        </div>
		<div id="applicant_deail_stats" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-orange-800">
                            <span class="font-weight-semibold" id="user_name_det"></span>'s Statistics 
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span class="text-orange-800">&times;</span></button>
                    </div>
                    <div class="modal-body" id="applicant_deail_stats_details" style="max-height: 500px; overflow-y: auto; padding-bottom: 0 !important;">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-orange-800 legitRipple" data-dismiss="modal">CLOSE
                        </button>
                    </div>

                </div>
            </div>
        </div>
		<div class="overlay"></div>
        <!-- /user statistics modal -->
@endsection

@section('js_file')
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('js/donut_chart.js') }}"></script>
@endsection

@section('script')
		
    <script>
        document.getElementById('openLink').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('openForm').submit();
        });
        document.getElementById('closeLink').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('closeForm').submit();
        });
        
            document.getElementById('openLinkUpdate').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('openFormUpdate').submit();
        });
        document.getElementById('reOpenLinkUpdate').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('reOpenFormUpdate').submit();
        });
        document.getElementById('closeLinkUpdate').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('closeFormUpdate').submit();
        });
        
        document.getElementById('openLinkWeekly').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('openFormWeekly').submit();
        });
        document.getElementById('closeLinkWeekly').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('closeFormWeekly').submit();
        });
        // monthly
        document.getElementById('openLinkMonthly').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('openFormMonthly').submit();
        });
        document.getElementById('closeLinkMonthly').addEventListener('click', function(event) {
            event.preventDefault(); // prevent the default behavior of the link
            document.getElementById('closeFormMonthly').submit();
        });

        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';
            $('#all_users').DataTable({
                "aLengthMenu": [[5, 10, 50, 100], [5, 10, 50, 100]]
            });
        });

        // fetch applicant's statistics details
        $(document).on('click', '.user-statistics', function (event) {
            var user_key = $(this).data('user_key');
            var user_name = $(this).data('user_name');
            var start_date = $('#user_stats_start_date_value').val();
            var end_date = $('#user_stats_end_date_value').val();

            $('#user_name').html(user_name);

            $.ajax({
                url: "{{ route('userStatistics') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_key: user_key,
                    user_name: user_name,
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response){
                    $('#user_stats_details').html(response);
                    $('#user_s_date').html(start_date);
                    $('#user_e_date').html(end_date);
                },
                error: function(response){
                    let raw_html = '<p>WHOOPS! Something Went Wrong!!</p>';
                    $('#user_stats_details').html(raw_html);
                }
            });
        });

        $(document).on('click', '.applicant_stats_detail_home', function(event){
            var user_home = $(this).data('user_home');
            var user_key = $(this).data('user_key');
            var date_value = '';
            var date_value_end ='';
            var date_update_app='';
            if(user_key=='daily')
            {
                date_value = $("#daily_date_value").val();
                date_update_app =$(this).data('user_update');

            }
            else if(user_key=='weekly')
            {
                date_value = $("#weekly_date_value").val();
                date_update_app =$(this).data('user_update');

            }
            else if(user_key=='monthly')
            {
                date_value = $("#monthly_date_input").val();
                date_update_app =$(this).data('user_update');

            }
            else if(user_key=='aggregate')
            {

                date_value = $("#custom_start_date_value").val();
                date_value_end = $("#custom_end_date_value").val();
            }
            var user_name = $(this).data('user_detail');
            $.ajax({
                url: "{{ route('applicant_home_details_stats') }}",
                type: "GET",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_key: user_key,
                    user_name: user_name,
                    date_value:date_value,
                    user_home:user_home,
                    date_value_end:date_value_end,
                    update_nurse:date_update_app

                },
                success: function(response){
                    console.log(response.user_stats);
                    $('#applicant_deail_stats_details').html(response.user_stats);
                    $('#user_name_det').text(response.user_name);
                },
                error: function(response){
                    let raw_html = '<p>WHOOPS! Something Went Wrong!!</p>';
                    $('#applicant_deail_stats_details').html(raw_html);

                }
            });

        });

        $(document).on('click', '.applicant_daily_detail_stats', function (event) {
            var date_value='';
            var date_value_end='';
            var user_key = $(this).data('user_key');
            if(user_key=='daily')
            {
                date_value = $("#daily_date_value").val();
            }
            else if(user_key=='weekly')
            {
                date_value = $("#weekly_date_value").val();
            }
            else if(user_key=='monthly')
            {
                date_value = $("#monthly_date_input").val();
            }
            else if(user_key=='aggregate')
            {

                date_value = $("#custom_start_date_value").val();
                date_value_end = $("#custom_end_date_value").val();
            }
            var user_name = $(this).data('user_detail');
            $.ajax({
                url: "{{ route('applicant_details_stats') }}",
                type: "GET",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_key: user_key,
                    user_name: user_name,
                    date_value:date_value,
                    date_value_end:date_value_end,
                },
                success: function(response){
                    $('#applicant_deail_stats_details').html(response.user_stats);
                    $('#user_name_det').text(response.user_name);
                },
                error: function(response){
                    let raw_html = '<p>WHOOPS! Something Went Wrong!!</p>';
                    $('#applicant_deail_stats_details').html(raw_html);

                }
            });
        });
    </script>
@endsection
