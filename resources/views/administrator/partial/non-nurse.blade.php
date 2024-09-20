@extends('layouts.app')

@section('content')
    <div class="content-wrapper">

        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-inline">
                <div class="page-title">
                    <h5>
                        <i class="icon-arrow-left52 mr-2"></i>
                        <span class="font-weight-semibold">Applicants</span> - Statistics - Details
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Applicants</a>
                        <a href="#" class="breadcrumb-item">Statistics</a>
                        <span class="breadcrumb-item active">Details</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">


            <div class="card border-top-teal-400 border-top-3">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">Applicants -- {{$stats_type}} </h5>
                </div>
                <div class="card-header header-elements-inline">
                </div>
                <table class="table table-striped" id="applicant_stats_table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Postcode</th>
                        <th>Phone#</th>
                        <th>Source</th>
                        {{--                        <th>stage</th>--}}
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($userData as $data)
                        <tr>
                            <td>{{$data->applicant_added_date}}</td>
                            <td>{{$data->applicant_added_time}}</td>
                            <td>{{$data->applicant_name}}</td>
                            <td>{{$data->applicant_job_title}}</td>
                            <td>{{$data->job_category}}</td>
                            <td>{{$data->applicant_postcode}}</td>
                            <td>{{$data->applicant_phone}}</td>
                            <td>{{$data->applicant_source}}</td>
                            {{--                            <td><a href="{{url('notes_detail_applicant/'.$data->id)}}">ALl Notes</a></td>--}}
                            @php
                                if($stats_type!="quality_cleared"){


                                 if ($stats_type=='crm_request'){
                                     $crm_request=["cv_sent_request", "request_save"];
                                 }elseif ($stats_type=='crm_confirmation'){
                                     $crm_request=["request_confirm"];

                                 }elseif ($stats_type =='crm_req_reject'){
                                     $crm_request=["request_reject"];
                                 }elseif ($stats_type =='crm_start_date_hold'){
                                     $crm_request=["start_date_hold"];
                                 }elseif ($stats_type =='crm_declined'){
                                     $crm_request=["declined"];
                                 }elseif ($stats_type =='crm_dispute'){
                                     $crm_request=["dispute"];
                                 }elseif ($stats_type =='crm_paid'){
                                     $crm_request=["paid"];
                                 }elseif ($stats_type =='crm_rebook'){
                                     $crm_request=["rebook"];
                                 }elseif ($stats_type =='crm_not_attended'){
                                     $crm_request=["interview_not_attended"];
                                 }elseif ($stats_type =='crm_invoice'){
                                     $crm_request=["invoice"];
                                 }elseif ($stats_type =='crm_start_date'){
                                     $crm_request=["start_date"];
                                 }
                                 else{
                                  $crm_request=[''];
                                 }

                                 $noteLatest = \Horsefly\Crm_note::where('applicant_id', '=', $data->id)
                                  ->join('sales', 'sales.id', '=', 'crm_notes.sales_id')
                                  ->join('units', 'units.id', '=', 'sales.head_office_unit')
                                  ->join('applicants', 'applicants.id', '=', 'crm_notes.applicant_id')
                                  ->select('crm_notes.details', 'crm_notes.moved_tab_to','crm_notes.id','crm_notes.applicant_id')
                                  ->whereIn("crm_notes.moved_tab_to", $crm_request)
                                  ->whereMonth('crm_notes.created_at', $current_month)->whereYear('crm_notes.created_at', $current_year)
                                  ->orderBy('crm_notes.id', 'DESC')->first();

                                 }else{

                                    $noteLatest = \Horsefly\Quality_notes::where('applicant_id',$data->id)
                                    ->whereMonth('quality_notes.created_at', $current_month)->whereYear('quality_notes.created_at', $current_year)
                                    ->where('quality_notes.moved_tab_to','cleared')
                                    ->orderBy('quality_notes.id', 'DESC')->first();

                                 }
                            @endphp
                            @if($noteLatest!= null)
                                <td>{{$noteLatest->details}}|{{$noteLatest->moved_tab_to}}</td>
                            @else
                                <td>{{$data->applicant_notes}}</td>

                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>


        @endsection
        @section('script')

            <script>
                $(document).ready(function(){
                    $('#applicant_stats_table').DataTable();
                });
            </script>
@endsection
