@extends('layouts.app')

@section('style')

    <script>
        var table;
        $(document).ready(function() {
            $("#sale_filter_office").select2({ width: '99%' });
            $.fn.dataTable.ext.errMode = 'none';
            table = $('#sale_sample_1').DataTable({
                "aoColumnDefs": [
                    { "bSortable": false, "aTargets": [11,12] },
                    { "bSearchable": false, "aTargets": [11,12] }
                ],
                "bProcessing": true,
                "bServerSide": true,
                "aaSorting": [],
                "sPaginationType": "full_numbers",
                "sAjaxSource": "{{ url('get-all-on-hold-sales') }}",
                "aLengthMenu": [[10, 50, 100, 500], [10, 50, 100, 500]],
                "drawCallback": function( settings, json){
                    $('[data-popup="tooltip"]').tooltip();
                }
            });

            // table.destroy();

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
                        <span class="font-weight-semibold">All</span> - On Hold Sales
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Sales</a>
                        <a href="#" class="breadcrumb-item">Current</a>
                        <span class="breadcrumb-item active">On Hold</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">

            <!-- Default ordering -->
            <div class="card">
                <div class="card-header header-elements-inline">
                    <div class="col-md-2">
                        <h5 class="card-title">On Hold Sales</h5>
                    </div>
                    <div class="header-elements col-md-10 justify-content-end">
                        <div class="col-md-12" style="padding-right: 0 !important;">
                            <form class="form-inline" name="sale_filter_form" id="sale_filter_form">
                                <div class="col-md-2">
                                    <select name="job_category" id="job_category_filter" class="form-control select">
                                        <option value="">Job Category</option>
                                        <option value="nurse">Nurse</option>
                                        <option value="nonnurse">Non Nurse</option>
										<option value="chef">Chef</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="cv_sent_option" id="cv_sent_option" class="form-control select">
                                        <option value="">Sent CV Count</option>
                                        <option value="zero">Zero</option>
                                        <option value="not_max">Not Max</option>
                                        <option value="max">Max</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="user" id="sale_filter_user" class="form-control select">
                                        <option value="">Select User Name</option>
                                        @foreach($head_office_users as $user)
                                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="office" id="sale_filter_office" class="form-control select-fixed-single">
                                        <option value="">Select Head Office</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex" style="padding-right: 0 !important;">
                                    <button type="submit" class="btn btn-outline bg-teal-400 text-teal-400 border-teal-400 border-2 flex-grow-1 mr-2 search"><i
                                                class="fa fa-search"></i> Search</button>
                                    <button id="refresh_filters" class="btn btn-outline alpha-teal text-teal-400 border-teal-400 border-2 float-right"><i
                                                class="fas fa-sync"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body row pb-0">
                    <div class="col-md-6">
                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger border-0 alert-dismissible mb-0 p-2">
                                <button type="button" class="close p-2" data-dismiss="alert"><span>×</span></button>
                                <span class="font-weight-semibold">Error!</span> {{ $messsage }}
                            </div>
                        @endif
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success border-0 alert-dismissible mb-0 p-2">
                                <button type="button" class="close p-2" data-dismiss="alert"><span>×</span></button>
                                <span class="font-weight-semibold">Success!</span> {{ $message }}
                            </div>
                        @endif
                    </div>
                    
                </div>
                

                <table class="table table-hover table-striped table-responsive" id="sale_sample_1">
                    <thead>
                    <tr>
                        <th>Created Date</th>
                        <th>Updated Date</th>
                        <th>Category</th>
                        <th>Job Title</th>
                        <th>Head Office</th>
                        <th>Unit</th>
                        <th>Postcode</th>
                        <th>Type</th>
                        <th>Experience</th>
                        <th>Qualification</th>
                        <th>Salary</th>
                        <th>Sent CVs</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <!-- /default ordering -->

        </div>
        <!-- /content area -->

@endsection

@section('script')
<script>
    // create new note
    $(document).on('click', '.note_form_submit', function (event) {
        event.preventDefault();
        var note_key = $(this).data('note_key');
        var $note_form = $('#note_form'+note_key);
        var $note_alert = $('#note_alert' + note_key);
        var note_details = $.trim($("#note_details" + note_key).val());
        if (note_details) {
            $.ajax({
                url: "{{ route('module_note.store') }}",
                type: "POST",
                data: $note_form.serialize(),
                success: function (response) {
                    // $note_form.trigger('reset');
                    $note_alert.html(response);
                    setTimeout(function () {
                        $('#add_sale_note' + note_key).modal('hide');
                    }, 1000);
                },
                error: function (response) {
                    var raw_html = '<p class="text-danger">WHOOPS! Something Went Wrong!!</p>';
                    $note_alert.html(raw_html);
                }
            });
        } else {
            $note_alert.html('<p class="text-danger">Kindly Provide Note Details</p>');
        }
        $note_form.trigger('reset');
        setTimeout(function () {
            $note_alert.html('');
        }, 2000);
        return false;
    });

    // fetch notes history
    $(document).on('click', '.notes_history', function (event) {
        var sale = $(this).data('sale');

        $.ajax({
            url: "{{ route('unholdSalesNotes') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                module_key: sale,
                module: "Sale"
            },
            success: function(response){
                $('#onhold_sales_notes_history'+sale).html(response);
            },
            error: function(response){
                // console.log(response);

                var raw_html = '<p>WHOOPS! Something Went Wrong!!</p>';
                $('#onhold_sales_notes_history'+sale).html(raw_html);
            }
        });
    });

    // fetch head offices by selected user
    $(document).on('change', '#sale_filter_user', function (event) {
        var user_key = $(this).val();
        $(this).select({ width: 'resolve' });
        $.ajax({
            url: "{{ route('userOffices') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                user_key: user_key
            },
            success: function (response) {
                $("#sale_filter_office").html(response)
                $("#sale_filter_office").select2({ width: '99%' });
            },
            error: function (response) {
                alert("WHOOPS! Something went Wrong!!");
            }
        });
    });

    // reload table with search options
    $(document).on('click', '.search', function (event) {
        event.preventDefault();
        var form_data = $('#sale_filter_form').serialize();
        table.ajax.url( 'get-all-on-hold-sales?'+form_data ).load();
    });

    $(document).on('click', '#refresh_filters', function (event) {
        event.preventDefault();

        $('#job_category_filter, #cv_sent_option, #sale_filter_user').prop('selectedIndex',0);
        $('#job_category_filter, #cv_sent_option, #sale_filter_user').select2();
        $('#sale_filter_office').empty().append('<option value="">Select Head Office</option>');
        table.ajax.url( 'get-all-on-hold-sales' ).load();
    });
</script>
@endsection
