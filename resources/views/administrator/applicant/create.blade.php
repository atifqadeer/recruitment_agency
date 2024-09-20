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
                        <span class="font-weight-semibold">Applicant</span> - Add
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="#" class="breadcrumb-item">Applicants</a>
                        <span class="breadcrumb-item">Current</span>
                        <span class="breadcrumb-item active">Add</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">
            <!-- Centered forms -->
            @if(session()->has('applicant_add_error'))
                <div class="alert alert-danger">
                    {{ session()->get('applicant_add_error') }}
                </div>
            @endif
        <!-- For Validation Errors  -->
            <!-- ============================================================== -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        <button type="button" class="btn btn-danger"
                                data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#myModal"
                                style="background-color: #007bff;border: none;">Replace With Note</button>
                        <!-- Modal -->
                        <div class="modal fade" tabindex="-1" id="myModal" role="dialog">
                            <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Write A Note For Applicant</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Note Title</label>
                                            <input type="text" id="note_title_id" class="form-control" placeholder="NOTE TITLE">
                                        </div>
                                        <div class="form-group">
                                            <label>Write a Note</label>
                                            <textarea  id="duplicate_note_for_applicants_id" cols="30"
                                                       rows="5" class="form-control"
                                                       placeholder="WRITE A NOTE FOR DUPLICATE APPLICANTS HERE..."></textarea>
                                        </div>
                                        <input type="button" id="duplicate_note_id" class="btn btn-primary btn-block" value="Save"
                                               style="background-color: #007bff;">
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- Modal End -->
                    </ul>
                </div>
        @endif
        <!-- End Validation Errors  -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    <div class="header-elements-inline">
                                        <h5 class="card-title">Add Applicant</h5>
                                        <a href="{{ route('applicants.index') }}" class="btn bg-slate-800 legitRipple">
                                            <i class="icon-cross"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    {{ Form::open(array('route' => 'applicants.store','method' => 'POST','files' => true )) }}
                                        <div class="form-group">
                                            {{Form::label('job_title', 'Job Title', array('class'=>'col-form-label'))}}
                                            {{ Form::select('applicant_job_title',
                                            [
                                                '' => 'Select Job Title',
                                                'Nurses' => array('rgn' => 'RGN', 'rmn' => 'RMN','rnld' => 'RNLD',
                                                                   'senior nurse' => 'Senior Nurse','nurse deputy manager' => 'Nurse Deputy Manager','nurse manager' => 'Nurse Manager',
                                                                   'rgn/rmn' => 'RGN/RMN','rmn/rnld' => 'RMN/RNLD',
                                                                   'rgn/rmn/rnld' => 'RGN/RMN/RNLD', 'clinical lead' => 'Clinical Lead',
                                                                   'rcn' => 'RCN', 'peripatetic nurse' => 'Peripatetic Nurse',
                                                                   'unit manager' => 'Unit Manager','nurse specialist' => 'Nurse Specialist'),
                                                'Non-Nurses' => array('care assistant' => 'Care Assistant', 'senior care assistant' => 'Senior Care Assistant',
                                                                      'team lead' => 'Team Lead','deputy manager' => 'Deputy Manager',
                                                                      'registered manager' => 'Registered Manager', 'support worker' => 'Support Worker',
                                                                      'senior support worker' => 'Senior Support Worker', 'activity coordinator' => 'Activity Coordinator','nonnurse specialist' => 'Non-Nurse Specialist'),
											'Chef' => array('chef' => 'Chef', 'chef de partie' => 'Chef De Partie',
                                                                      'head chef' => 'Head Chef','sous chef' => 'Sous Chef','commis chef' => 'Commis Chef',
                                                                      ),
                                                ],
                                            null, array('class'=>'form-control form-control-select2','id'=>'app_job_title_spec')) }}
                                        </div>
										<div class="form-group" id="app_specialist">

                                    </div>
                                        <div class="form-group">
                                            {{ Form::label('name','Name') }}
                                            {{ Form::text('applicant_name', null, array('id' => 'name', 'class' => 'form-control',
                                             'placeholder' => 'ENTER APPLICANT NAME')) }}
                                        </div>
                                    <div class="form-group">
                                        {{ Form::label('email_address', 'Email Address', array('class'=>'col-form-label')) }}
                                        {{ Form::email('applicant_email', null, array('id'=>'email_address_id','class'=>'form-control',
                                        'placeholder' => 'ENTER APPLICANT EMAIL ADDRESS')) }}
                                    </div>
                                    
									<div class="form-group">
                                    {{ Form::label('source', 'Source') }}
                                    {!! Form::select('applicant_source', $applicant_source, null, array('id'=>'source','class'=>
										'form-control','required',
                                        'placeholder' => 'SELECT APPLICANT SOURCE')) !!}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('postcode', 'Postcode') }}
                                        {{ Form::text('applicant_postcode', null, array('id'=>'postcode_id', 'class'=>'form-control',
                                        'placeholder' => 'ENTER APPLICANT POSTCODE')) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('phnumber', 'Mobile Number') }}
                                        {{ Form::text('applicant_phone', null, array('id'=>'phone_number_id','class'=>'form-control',
                                        'placeholder' => 'ENTER APPLICANT PHONE NUMBER')) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('mobile','Home Number') }}
                                        {{ Form::text('applicant_homePhone',null,array('id'=>'home_number_id','class'=>'form-control',
                                        'placeholder' => 'ENTER APPLICANT MOBILE NUMBER')) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('attachment', 'Attach CV:') }}
                                        {{ Form::file('applicant_cv',array('class'=>'form-input-styled')) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('applicant_notes', 'Add Notes:') }}
                                        {{ Form::textarea('applicant_notes',null,
                                        array('class'=>'form-control form-input-styled','rows' => '7','cols' => '20')) }}
                                    </div>
                                    <div class="text-right">
                                        {{ Form::button('Save <i class="icon-paperplane ml-2"></i>',['type'=>'submit','class'=>'btn bg-teal legitRipple']) }}
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
