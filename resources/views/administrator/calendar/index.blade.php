@extends('layouts.app')
@section('style')
{{--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />--}}

{{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />--}}
{{--<link rel='stylesheet' href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.min.css" />--}}
{{----}}
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />--}}
<link rel='stylesheet' href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.min.css" />

@endsection
{{--<head>--}}
{{--    <title>Laravel Fullcalender Tutorial Tutorial - ItSolutionStuff.com</title>--}}
{{--    <meta name="csrf-token" content="{{ csrf_token() }}">--}}

{{--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>--}}
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>--}}

{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>--}}
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />--}}
{{--</head>--}}
{{--<body>--}}
@section('content')
    <div class="content-wrapper">
<div class="container">
    <h1> FullCalender Testing interviews booked</h1>
    <div id='calendar'></div>
</div>
        <!-- Your Modal -->
        <div class="modal fade" id="eventDetailsModal" tabindex="-1" role="dialog" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="eventDetailsBody">
                        <!-- Details will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        @endsection
@section('script')
{{--            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>--}}
            <script src="https://momentjs.com/downloads/moment.min.js"></script>
            <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.js'></script>


            <script>
                $(document).ready(function() {
                    $('#calendar').fullCalendar({
                        header: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'month,weekly'
                        },
                        minTime: '09:00:00', // Set the minimum time to 9 am
                        maxTime: '18:00:00', // Set the maximum time to 6 pm
                        // events: [
                        //     {
                        //         title: 'Demo Event 1',
                        //         start: '2024-02-04T10:00:00', // February 4 at 10 am
                        //         end: '2024-02-04T12:00:00'   // February 4 at 12 pm
                        //     },
                        //     {
                        //         title: 'Demo Event 2',
                        //         start: '2024-02-08T14:00:00', // February 8 at 2 pm
                        //         end: '2024-02-08T16:00:00'   // February 8 at 4 pm
                        //     },
                        //     {
                        //         title: 'Demo Event 3',
                        //         start: '2024-02-29T11:30:00', // February 9 at 11:30 am
                        //         end: '2024-02-27T13:30:00'   // February 9 at 1:30 pm
                        //     },
                        //     // Add more demo events as needed
                        // ],
                        // Other options and callbacks go here
                        events: [
                            {
                                    title: 'Demo Event 3',
                                    start: '2024-02-29T11:30:00', // February 9 at 11:30 am
                                    end: '2024-02-27T13:30:00'   // February 9 at 1:30 pm
                                },
                                @foreach($interviews as $interview)
                            {
                                title: '{{ $interview->applicant->applicant_name ?? "No Name" }} - {{ $interview->sale->office ? $interview->sale->office->office_name : "No Office" }}' +
                                    ' - {{ \Carbon\Carbon::parse($interview->schedule_date)->format("d-m-Y") }}',
                                start: '{{ \Carbon\Carbon::parse($interview->schedule_date)->toDateString() }}T{{ $interview->schedule_time }}',
                                end: '{{ \Carbon\Carbon::parse($interview->schedule_date)->toDateString() }}T{{ $interview->schedule_time }}',
                                applicantName: '{{ $interview->applicant->applicant_name ?? "No Name" }}',
                                saleName: '{{ $interview->sale->office ? $interview->sale->office->office_name : "No Office" }}',
                                date: '{{ \Carbon\Carbon::parse($interview->schedule_date)->format("d-m-Y") }}',
                                // You can include other properties like 'id', 'applicant_id', etc.
                            },
                            @endforeach
                        ],
                        eventRender: function(event, element) {
                            console.log('Rendering event:', event);
                        },
                        eventClick: function(calEvent, jsEvent, view) {
                            // Display details in a modal or a popup
                            var modal = $('#eventDetailsModal');
                            var modalBody = $('#eventDetailsBody');

                            // Customize this to display the details you want
                            var details = '<p><strong>Applicant Name:</strong> ' + calEvent.applicantName + '</p>' +
                                '<p><strong>Sale Name:</strong> ' + calEvent.saleName + '</p>' +
                                '<p><strong>Date:</strong> ' + calEvent.date + '</p>';

                            modalBody.html(details);
                            modal.modal('show');
                        },
                    });
                });

            </script>
@endsection

