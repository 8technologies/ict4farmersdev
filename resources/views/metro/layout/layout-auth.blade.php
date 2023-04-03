@extends('metro.layout.base-layout')

<body id="kt_body" class="bg-body">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Authentication - Sign-in -->
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <!--begin::Aside-->
            <div class="d-flex flex-column flex-lg-row-auto w-xl-600px positon-xl-relative py-5 py-md-20"
                style="    background-size:     cover;                      /* <------ */
                background-repeat:   no-repeat;
                background-position: center center; background-color: #baf0d4;background-image: url({{ url('assets/images/digital-value-chain-for-small-holder-farmers.png') }})">

            </div>
            <!--end::Aside-->
            <!--begin::Body-->
            <div class="d-flex flex-column flex-lg-row-fluid ">
                <!--begin::Content-->
                <div class="d-flex flex-center flex-column flex-column-fluid">
                    <!--begin::Wrapper-->
                    <div class="w-lg-500px p-10 p-lg-15 mx-auto">

                        <div class="row">
                            <div class="col-12">
                                <center>
                                    <a href="{{ url("market") }}" class="py-9 mb-5">
                                        <img alt="Logo" src="{{ url('assets/images/logo.png') }}" class="h-90px" />
                                    </a>
                                </center>

                                <h1 class="fw-bolder fs-2qx mb-0 text-center pb-0 mt-5 mb-2 text-uppercase "
                                    style="color: #145a32;">Welcome
                                    to ICT4farmers</h1>

                                <i>
                                    <p class="fw-light  text-center mt-1 pt-0" style="color: #636363; font-size:16px;">
                                        "Making ICT work for farmers."
                                    </p>
                                </i>


                                <div class="separator my-10 separator-dashed border-1 border-primary  "></div>


                                @if (session('success_message'))
                                    <div class="alert alert-success">{{ session('success_message')}}</div>
                                @endif

                            </div>
                        </div>

                        <!--begin::Form-->
                        @yield('content')
                        <!--end::Form-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Content-->
                <!--begin::Footer-->
                <div class="d-flex flex-center flex-wrap fs-6 p-5 pb-0">
                    <!--begin::Links-->
                    <div class="d-flex flex-center fw-bold fs-6">
                        <a href="javascript:;" class="text-muted text-hover-primary px-2" target="_blank">About</a>
                        <a href="javascript:;" class="text-muted text-hover-primary px-2" target="_blank">Support</a>
                        <a href="javascript:;" class="text-muted text-hover-primary px-2"
                            target="_blank">8Technologies</a>
                    </div>
                    <!--end::Links-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Authentication - Sign-in-->
    </div>
    <!--end::Root-->
    <!--end::Main-->
    <!--begin::Javascript-->

    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="{{url('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ url('assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ url('assets/js/custom/authentication/sign-in/general.js') }}"></script>
    <!--end::Page Custom Javascript-->
    <!--end::Javascript-->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
        });

    </script>
</body>
