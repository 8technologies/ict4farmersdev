<?php
use App\Models\Utils;
$steps = Utils::get_wizard_actions(Admin::user()->id);
?>

<link rel="stylesheet" href="{{ url('/assets/css/bootstrap.css') }}">

@if (isset($vendor_requests))
    <div class="row">
        <div class="col-12">
            {{-- alert --}}
            @if ($vendor_requests > 0)
                <div class="alert bg-primary alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                    <h5>Alert!</h5>
                    You have {{ $vendor_requests }} new vendor requests. <a
                        href="{{ admin_url('system-users?&farmer_status%5B%5D=Requested') }}">View
                        requests</a>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="row ">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-success">
                    <h2 class="h4 text-bold m-0 border-bottom p-2 border-primary">Production</h2>
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-4 col-md-4 my-admin-item">
                                <a href="{{ admin_url('farms') }}">
                                    <img width="100%" src="{{ url('assets/images/admin/farm.png') }}">
                                    <h3 class="my-title-1 text-center">Farms</h3>
                                </a>
                            </div>

                            <div class="col-4  col-md-4 mt-5 " style="">
                                <img width="100%" class=" "
                                    src="{{ url('assets/images/admin/arrow-right.png') }}">
                            </div>
                            <a href="{{ admin_url('gardens') }}" class="col-4  col-md-4 my-admin-item">
                                <img width="100%" src="{{ url('assets/images/admin/enterprise.png') }}">
                                <h3 class="my-title-1 text-center">Enterprises</h3>
                            </a>
                        </div>

                        <div class="row">
                            <div class="col-4 col-md-4">

                            </div>
                            <div class="col-4 col-md-4 ">

                            </div>
                            <div class="col-4  col-md-4 pl-5 pl-md-10 pt-2 pb-2" style=" ">
                                <img height="70px" src="{{ url('assets/images/admin/arrow-bottom.png') }}">
                            </div>
                        </div>

                        <div class="row">
                            <a href="{{ admin_url('garden-production-records') }}" class="col-4 col-md-4 my-admin-item">
                                <img width="100%" src="{{ url('assets/images/admin/records.png') }}">
                                <h3 class="my-title-1 text-center">Production Records</h3>
                            </a>
                            <div class="col-4 col-md-4 " style="padding-top: 5rem">
                                <img width="100%" class=" "
                                    src="{{ url('assets/images/admin/arrow-left.png') }}">
                            </div>
                            <a href="{{ admin_url('garden-activities') }}" class="col-4 col-md-4 my-admin-item">
                                <img width="100%" src="{{ url('assets/images/admin/tasks.png') }}">
                                <h3 class="my-title-1 text-center ">Activities</h3>
                            </a>
                        </div>
                    </div><!-- /.box-body -->
                </div>
            </div>

            <div class="col-12 col-md-4">

                <div class="box box-success">
                    <div class="with-border p-0">
                        <h2 class="h4 text-bold m-0 border-bottom p-2 border-primary">Management</h2>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body" style="display: block;">

                        <div class="row">
                            <a href="{{ admin_url('financial-records') }}"
                                class="col-4 p-0  col-md-12 my-admin-item text-center mb-md-5">
                                <img width="32%" src="{{ url('assets/images/admin/money.gif') }}">
                                <h3 class="my-title-1 text-center " style="">Financial records</h3>
                            </a>

                            <a href="{{ admin_url('my-workers') }}"
                                class="col-4 col-md-12  my-admin-item text-center  mb-md-5">
                                <img width="45%" src="{{ url('assets/images/admin/workers.png') }}">
                                <h3 class="my-title-1 text-center ">Farm workers</h3>
                            </a>

                            <a href="{{ admin_url('products') }}"
                                class="col-4 col-md-12 my-admin-item text-center  mb-md-5">
                                <img width="45%" src="{{ url('assets/images/admin/market.png') }}">
                                <h3 class="my-title-1 text-center ">Market place</h3>
                            </a>


                        </div>





                    </div>
                </div>
            </div>
        </div>

        {{--     <div class="row ">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-6 col-md-4">
                        <div class="card border border-success mb-4 ">
                            <a href="{{ admin_url('gardens') }}" class=" card-body py-1 my-0">
                                <h3 class="p-0 m-0 text-dark">20</h3>
                                <h3 class="p-0 m-0 text-primary h4 text-bold">Enterprises</h3>
                                <p class="text-dark">2 Cattle, 4 Gardens</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <a href="{{ admin_url('garden-activities') }}" class="card border border-success  mb-4">
                            <div class=" card-body py-1 my-0">
                                <h3 class="p-0 m-0 text-dark">12</h3>
                                <h3 class="p-0 m-0 text-primary h4 text-bold">Activities</h3>
                                <p class="text-dark">23 Pending, 4 Done, <span class="text-danger">80 Missed</span></p>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="card border border-success  mb-4">
                            <a href="{{ admin_url('financial-records') }}" class=" card-body py-1 my-0">
                                <h3 class="p-0 m-0 text-dark">UGX 120,000</h3>
                                <h3 class="p-0 m-0 text-primary h4 text-bold">Total Profit or Loss</h3>
                                <p class="text-dark">UGX 20,000 Income, UGX 45,000 Expenses</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div> --}}


    </div>


    <div class="col-md-4">

        <div class="card mb-4">
            <div class="card-body">
                <h2 class=" text-primary text-bold m-0 py-2 mb-2">Getting Started Checklist</h2>
                @foreach ($steps as $s)
                    <div class="form-check">
                        <input @if ($s->is_done == 1) checked @endif class="form-check-input " readonly
                            type="checkbox" value="" id="box-{{ $s->id }}">
                        <label class="form-check-label text-dark pl-4 pl-md-5"
                            style="font-weight: 400; font-size: 1.4rem; line-height: 1.5;"
                            for="box-{{ $s->id }}">
                            {{ $s->title }}
                        </label>
                        <a class="text-right float-right text-bold" href="javascript:;">
                            <i class="fa fa-youtube-play"></i>
                            Learn how</a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="box box-success">
            <div class="box-body" style="display: block;">

                <div id='loading'>Refresh this page to see the calender...</div>

                <div id='calendar'></div>

            </div>
        </div>

    </div>
</div>







<script>
    $(document).ready(function() {
        var data = JSON.parse('<?= json_encode($events) ?>');

        function show_activity(x) {
            const eve = data[x];

            $.alert({
                title: eve.type,
                content: eve.details,
                closeIcon: true,
                buttons: {
                    edit: {
                        btnClass: 'btn-primary',
                        text: 'UPDATE ACTIVITY STATUS',
                        action: function() {
                            submit_activity(eve.activity_id);
                        }
                    },
                    CLOSE: function() {

                    },
                }
            });

        }

        $('.activity-item').click(function(e) {
            e.preventDefault();
            show_activity(e.currentTarget.dataset.id);

        });



        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {

            headerToolbar: {
                left: 'title',
                right: 'prev today next'
            },

            editable: false,
            selectable: false,
            selectMirror: true,
            nowIndicator: true,
            displayEventTime: true,
            events: data,

            eventClick: function(arg) {

                // opens events in a popup window
                //window.open(arg.event.url, 'google-calendar-event', 'width=700,height=600');
                arg.jsEvent.preventDefault() // don't navigate in main tab

                const eve = arg.event._def;
                const activity_id = eve.extendedProps.activity_id;


                $.alert({
                    title: eve.extendedProps.type,
                    content: eve.extendedProps.details,
                    closeIcon: true,
                    buttons: {
                        edit: {
                            btnClass: 'btn-primary',
                            text: 'UPDATE ACTIVITY STATUS',
                            action: function() {
                                submit_activity(activity_id);
                            }
                        },
                        CLOSE: function() {

                        },
                    }
                });
            },

            loading: function(bool) {
                document.getElementById('loading').style.display =
                    bool ? 'block' : 'none';
            }

        });

        calendar.render();
    });

    function submit_activity(x) {

        $.confirm({
            title: 'Activity status submission',
            content: '' +
                '<form method="GET" action="<?= admin_url('calender-activity-edit') ?>" class="formName">' +

                '<div class="form-group">' +
                '<div class="form-check form-check-inline">' +
                '<input required class="form-check-input" type="radio" id="done_status" name="done_status" value="1">' +
                '<label class="form-check-label" for="done_status">Done</label></div>' +
                '</div>' +

                '<div class="form-group">' +
                '<div class="form-check form-check-inline">' +
                '<input required class="form-check-input" type="radio" id="done_status_not" name="done_status" value="0">' +
                '<label class="form-check-label" for="done_status_not">Not Done (Missed)</label></div>' +
                '</div>' +


                '<div class="form-group">' +
                '<label>Activity remarks</label>' +
                '<input name="done_details" placeholder="Remarks about this activity"  class="name form-control" required />' +
                '</div>' +

                '<input name="id" type="hidden" value="' + x + '">' +


                '<button class="btn-lg float-right btn btn-primary mt-2" type="submit">SUBMIT</button>' +


                '</form>',
            buttons: {
                cancel: function() {
                    //close
                },
            },

        });
    }
</script>
