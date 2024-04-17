@php

$_title = isset($title) ? $title : 'No title';
//$item->name = 'Sexy Bikini Satin Fabric Three Piece Swimsuit Pure Color Customized Swimwear Bathing Suit';
$_img = isset($item) ? $item->get_thumbnail() : 'no_image.jpg';
$_link = isset($item) ? url($item->slug) : 'javascript:;';

@endphp 

<div class="">
    <a href="{{ $_link }}">
        <img data-src="{{ $_img }}" src="no_image.jpg"
            class="img-fluid ml-2 ml-md-5 mr-2 mr-md-5 text-overflow lazy" alt="{{ $_title }}">
        <h3 class="mb-0 my-3 mb-md-0  fw-normal text-hover-primary"
            style="font-size: 16px; line-height: 20px; color: #888888;">{{ $item->name }}</h3>

        <div class="row">
            <div class="col-8">
                <h3 class="my-1  text-primary fw-bolder " style="font-size: 25px;">
                    {{ config('app.currency') }}{{ $item->price }}</h3>
            </div>

            <div class="col-3 p-0">
                <img class="img-fluid rounded-circle" style="float: right" width="60%"
                    src="{{ url('assets/images/verified.png') }}" alt="">
            </div>

        </div>



    </a>


    <div class="row">
        <div class="col-3">
            <a href="javascript:;">
                <img class="img-fluid rounded-circle" src="{{ url('no_image.jpg') }}" alt="">
            </a>
        </div>
        <div class="col-9 p-0 m-0 pb-2">
            <p class="text-dark p-0 m-0"><a href="javascript:;"
                    class="text-dark p-0 m-0 fs-5 text-hover-primary text-capitalize"
                    style="line-height: .7rem;">{{ $item->seller_name }}</a></p>
            <p class="text-gray-600 p-0 m-0"><a href="javascript:;"
                    class="text-gray-600 p-0 m-0 fs-5 text-hover-primary"
                    style="line-height: .7rem;">{{ $item->location->get_name() }}</a>
            </p>
        </div>
       {{--  <div class="row">
            <div class="col-8 ">
                <a href="" class="btn btn-sm btn-outline btn-outline-primary d-block mt-2 mb-1  ">Contact
                    Seller</a>
            </div>
            <div class="col-4 mt-2 pe-0">
                <a href="#" style="float: right"
                    class="btn btn-icon btn-outline btn-outline-secondary btn-sm ps-2 "><i
                        class="fa text-dark fa-eye fs-4 me-2"></i></a>

            </div>
        </div> --}}
    </div>

</div>
