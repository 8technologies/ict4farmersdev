<?php
use App\Models\Banner;
use App\Models\User;
use App\Models\Category;
$show_logo = true;
$u = Admin::user();
if ($u == null) {
    $u = new User();
}

$acive_classes = ' active bg-light border-bottom border-5 fw-bolder border-primary rounded-0 ';

$banners_all = Banner::all();
$categories = Category::all();

$banners_1 = [];
$banners_2 = [];
$banners_3 = [];
$banners_3_tab_active = '';
$banners_2_tab_active = '';
$banners_1_tab_active = '';

$tab_seg = (int) request()->segment(2);
$seg_1 = request()->segment(1);
$seg_2 = request()->segment(2);

foreach ($banners_all as $key => $value) {
    if ($value->id < 17) {
        if ($value->id < 17) {
            $banners_1[] = $value;
            if ($tab_seg == ((int) $value->id)) {
                $banners_1_tab_active = 'my-active-menu';
            }
        }
    } elseif ($value->id < 17 * 2) {
        $banners_2[] = $value;
        if ($tab_seg == ((int) $value->id)) {
            $banners_2_tab_active = 'my-active-menu';
        }
    } elseif ($value->id < 17 * 3) {
        $banners_3[] = $value;
        if ($tab_seg == ((int) $value->id)) {
            $banners_3_tab_active = 'my-active-menu';
        }
    }
}

$is_dashboard = false;
$is_logged_in = false;
$dashboard_segs = ['dashboard'];
$u = Admin::user();
if ($u != null) {
    $is_logged_in = true;
} else {
    $is_logged_in = false;
}
if (in_array($seg_1, $dashboard_segs)) {
    $is_dashboard = true;
}

?>
{{-- 
    
    
    --}}
<div id="kt_header" style="" class="header align-items-stretch">
    <div class="container-xxl d-flex align-items-stretch justify-content-between">
        @if (request()->segment(1) != 'dashboard')
            <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 me-lg-15">
                <a href="{{ url('/market') }}">
                    <img alt="Logo" src="{{ url('/assets/images/logo-2.png') }}" class="h-30px h-lg-50px" />
                </a>
            </div>
        @endif

        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">

            <div class="d-none d-md-flex align-items-stretch" id="kt_header_nav">

                @if (!$is_dashboard)

                    <div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu"
                        data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                        data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="end"
                        data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-swapper="true"
                        data-kt-swapper-mode="prepend"
                        data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                        <!--begin::Menu-->
                        <div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch"
                            id="#kt_header_menu" data-kt-menu="true">

                            <div data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                class="menu-item menu-lg-down-accordion me-lg-1  bg-hover-white bg-white">
                                <span class="menu-link py-3  bg-hover-white bg-white">
                                    <span class="menu-title fw-normal fs-5 lh-1 text-dark">Browse by categories</span>
                                    <span class="menu-arrow d-lg-none"></span>
                                    <span><i class="bi bi-chevron-down fs-3 ms-3 fw-bold h4"></i></span>
                                </span>
                                <div
                                    class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown w-100 w-lg-600px p-5 p-lg-5 rounded-0">
                                    <!--begin:Row-->
                                    <div class="row" data-kt-menu-dismiss="true">

                                        @foreach ($categories as $cat)
                                            @php
                                                $p_id = (int) $cat->parent;
                                            @endphp
                                            @if ($p_id == 0)
                                                <div class="col-lg-4 border-left-lg-1">
                                                    <div class="menu-inline menu-column menu-active-bg">
                                                        <div
                                                            class="menu-item border border-3 border-right-0 border-top-0 border-bottom-0 border-primary p-0">
                                                            <a href="{{ url($cat->slug) }}"
                                                                class="menu-link fw-bolder bg-hover=white bg-white text-hover-primary text-dark $cat->slug">{{ $cat->name }}</a>
                                                        </div>

                                                        @foreach ($cat->kids as $kid)
                                                            <div class="menu-item py-1 bg-white">
                                                                <a href="{{ $cat->slug }}"
                                                                    class="menu-link p-0 bg-white">
                                                                    <span class="menu-bullet bg-white">
                                                                        <span class="bullet bullet-dot"></span>
                                                                    </span>
                                                                    <span class="menu-title">{{ $kid->name }}</span>
                                                                </a>
                                                            </div>
                                                        @endforeach

                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach



                                    </div>
                                    <!--end:Row-->
                                </div>
                            </div>
                            <div class="menu-item"
                                style="   flex-grow: 0;
                    flex-shrink: 0;
                    flex-basis: 80%;">
                                <div class="input-group flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-search fs-4"></i></span>
                                    <div class="overflow-hidden flex-grow-1">
                                        <select data-allow-clear="true"
                                            class="form-select form-select-md rounded-start-0 " data-control="select2"
                                            data-placeholder="Search for products & sellers" id="select-search">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                @endif
            </div>

            <div class="d-flex align-items-stretch flex-shrink-0">

                <!--begin::Theme mode-->
                <div class="d-none d-md-flex align-items-center ms-1 ms-lg-3">
                    <div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">

                        @if ($is_logged_in)
                            <a href="{{ url('admin') }}" class="btn btn-primary btn-sm bg"
                                style="margin-left: .8rem">My Dashboad</a>



                            {{-- <a href="{{ url('admin') }}"
                                class="ms-5 btn btn-outline btn-outline-primary btn-sm text-hover-white">Manage your
                                farms</a> --}}


                            {{-- <a href="{{ url('login?submit_to=admin') }}"
                                class="btn btn-icon btn-sm btn-secondary p-0 border border-primary rounded-circle"
                                style="margin-left: .7rem" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                data-bs-dismiss-="click" title="My chats">
                                <i class="bi bi-chat fs-4 text-primary"></i></a> --}}
                        @else
                            <a href="{{ url('login') }}" class="btn btn-primary btn-sm bg" style="margin-left: .8rem">
                                Login here to access your dashboard</a>


                            {{-- <a href="{{ url('login?submit_to=admin') }}"
                                class="ms-5 btn btn-outline btn-outline-primary btn-sm text-hover-white">Manage
                                your farms</a> --}}

                            <a href="javascript:;"
                                class="btn btn-icon btn-sm btn-secondary p-0 border border-primary rounded-circle"
                                style="margin-left: .7rem" data-bs-toggle="tooltip" data-bs-trigger="hover"
                                data-bs-dismiss-="click" title="Help">
                                <i class="bi bi-question-lg fs-4 text-primary"></i></a>
                        @endif







                    </div>
                </div>



                <!--begin::Header menu toggle-->
                <div class="d-flex align-items-center d-lg-none ms-2 me-n3" title="Show header menu">
                    <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px"
                        id="kt_header_menu_mobile_toggle">
                        <!--begin::Svg Icon | path: icons/duotune/text/txt001.svg-->
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M13 11H3C2.4 11 2 10.6 2 10V9C2 8.4 2.4 8 3 8H13C13.6 8 14 8.4 14 9V10C14 10.6 13.6 11 13 11ZM22 5V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4V5C2 5.6 2.4 6 3 6H21C21.6 6 22 5.6 22 5Z"
                                    fill="black" />
                                <path opacity="0.3"
                                    d="M21 16H3C2.4 16 2 15.6 2 15V14C2 13.4 2.4 13 3 13H21C21.6 13 22 13.4 22 14V15C22 15.6 21.6 16 21 16ZM14 20V19C14 18.4 13.6 18 13 18H3C2.4 18 2 18.4 2 19V20C2 20.6 2.4 21 3 21H13C13.6 21 14 20.6 14 20Z"
                                    fill="black" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </div>
                </div>
                <!--end::Header menu toggle-->
            </div>

            <!--end::Toolbar wrapper-->
        </div>
        <!--end::Wrapper-->
    </div>
</div>


@section('footer')
    <script>
        $(document).ready(function() {

            var base_url = '{{ url('') }}';

            $('#select-search').select2({
                ajax: {
                    url: base_url + '/api/products',

                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },

                    dataType: 'json'
                },
                templateResult: function(item) {
                    return format(item);
                }
            }).on('change', function(e) {
                var val = $('#select-search').val();
                window.location.href = base_url + "/" + val;
            });

            // // Format options
            const format = (item) => {

                var img = $("<img>", {
                    class: "rounded me-2",
                    width: 40,
                    src: item.img
                });
                var span = $("<span>", {
                    text: " " + item.name
                });
                span.prepend(img);
                return span;
            }

            // // Init Select2 --- more info: https://select2.org/
            // $('#kt_docs_select2_users').select2({
            //     templateResult: function(item) {
            //         return format(item);
            //     }
            // });

        });
    </script>
@endsection
