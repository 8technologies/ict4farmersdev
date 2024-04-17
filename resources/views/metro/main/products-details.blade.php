@php
use App\Models\Product;
use App\Models\Attribute;
use App\Models\Utils;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Chat;

$slug = request()->segment(1);
$pro = Product::where('slug', $slug)->first();

if ($pro == null) {
    $pro = Product::where('id', $slug)->first();
}

if ($pro == null) {
    die("Product not found. Maybe it's already deleted.");
}

if ($pro) {
    if (!$pro->owner) {
        dd('owner not found.');
    }
} else {
    die("Product not found. Maybe it's already deleted.");
}

$products = [];
$conds['category_id'] = 1;
$products = Product::where($conds)->paginate(4);

$images = $pro->get_images();
$description = $pro->description;

$pro->init_attributes();
$attributes = json_decode($pro->attributes);
if ($attributes == null) {
    $attributes = [];
}

$url = $_SERVER['REQUEST_URI'];

$is_logged_in = false;

$user = Auth::user();
$message_link = url('/register');
$message_text = 'CHAT WITH SUPPLIER';
if ($user != null) {
    if (isset($user->id)) {
        $is_logged_in = true;
        if ($pro->user_id == $user->id) {
            $message_link = 'javascript:;';
            $message_text = 'This is your product.';
        } else {
            $chat_thred = Chat::get_chat_thread_id($user->id, $pro->user_id, $pro->id);
            $message_link = url('dashboard/chats/' . $chat_thred);
        }
    }
}
$first_seen = false;

$products = [];
$products = Product::all();

$recomended = [];
foreach ($products as $key => $p) {
    if (count($recomended) < 6) {
        $recomended[] = $p;
        continue;
    }
}

@endphp

@extends('metro.layout.layout-main')
@section('main-content')
@section('footer')
    <script src="assets/plugins/custom/fslightbox/fslightbox.bundle.js"></script>
@endsection


<div class="row  mt-5">
    <div class="col-12">
        <ol class="breadcrumb text-muted fs-6 fw-bold">
            <li class="breadcrumb-item pe-3"><a href="{{ url('') }}" class="pe-3">Home</a></li>
            <li class="breadcrumb-item pe-3"><a href="{{ url($pro->pro_category->slug) }}"
                    class="pe-3">{{ $pro->pro_category->name }}</a></li>
            <li class="breadcrumb-item px-3 text-muted">{{ $pro->name }}</li>
        </ol>
    </div>
</div>
<div class="row  mt-5">
    <div class="col-md-4 bg-white py-5 ">
        <div id="kt_carousel_1_carousel" class="carousel carousel-custom " data-bs-ride="carousel"
            data-bs-interval="8000">
            <div class="carousel-inner slider-arrow">
                @foreach ($images as $img)
                    @php
                    
                        $active = '';
                        if (!$first_seen) {
                            $active = ' active ';
                            $first_seen = true;
                        }
                    @endphp
                    <div class="carousel-item  <?= $active ?>  ">

                        <a class="d-block overlay" data-fslightbox="gallery" href="{{ url('public/storage/'. $img->src) }}">
                            <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded">
                                <img class="d-block w-100" src="{{ url('public/storage/'. $img->thumbnail) }}" alt="Product photo">
                            </div>
                            <div class="overlay-layer card-rounded bg-dark bg-opacity-25 shadow">
                                <i class="bi bi-eye-fill text-white fs-3x"></i>
                            </div>
                        </a>

                    </div>
                @endforeach
            </div>
            <div class="p-0 m-0 carousel-indicators carousel-indicators-dots bg-dark">
                @php
                    $_count = 0;
                    $active_class = 'active';
                @endphp
                @foreach ($images as $img)
                    <div data-bs-target="#kt_carousel_1_carousel" data-bs-slide-to="{{ $_count }}"
                        class="ms-{{ $_count }} {{ $active_class }}">
                        {{-- <img class="d-block w-100" src="{{ $img->thumbnail }}" alt="details" alt="First slide"> --}}
                    </div>
                    @php
                        $_count++;
                        $active_class = '';
                    @endphp
                @endforeach
            </div>
        </div>



    </div>
    <div class="col-md-5 bg-white pt-5">

        <h1 class=" h1" style="font-weight: 800;">{{ $pro->name }}</h1>
        <div class="separator my-5"></div>
        <h2 class="display-6 text-primary h2">{{ config('app.currency') . ' ' }} {{ $pro->price }}
        </h2>
        <div class="separator my-5"></div>

        <table class="table table-striped table-sm">
            @foreach ($attributes as $item)
                @if ($item->type == 'text' || $item->type == 'textarea')
                    <tr>
                        <td>
                            <h6>{{ $item->name }}:</h6>
                        </td>
                        <td><span>{{ $item->value }} {{ $item->units }}</span></td>
                    </tr>
                @elseif($item->type == 'number')
                    <tr>
                        <td>
                            <h6>{{ $item->name }}: </h6>
                        </td>
                        <td>
                            <p>{{ $item->value }} {{ $item->units }}</p>
                        </td>
                    </tr>
                @elseif($item->type == 'select')
                    <tr>
                        <td>
                            <h6>{{ $item->name }}: </h6>
                        </td>
                        <td>
                            <p>{{ $item->value }} {{ $item->units }}</p>
                        </td>
                    </tr>
                @elseif($item->type == 'radio')
                    <tr>
                        <td>
                            <h6>{{ $item->name }}: </h6>
                        </td>
                        <td>
                            <p>{{ $item->value }} {{ $item->units }}</p>
                        </td>
                    </tr>
                @elseif($item->type == 'checkbox')
                    <tr>
                        <td>
                            <h6 class="mr-3">{{ $item->name }}: </h6>
                        </td>
                        <td> @php
                            if ($item->value) {
                                $i = 0;
                                foreach ($item->value as $key => $value) {
                                    $i++;
                                    echo $value;
                                    if ($i != count($item->value)) {
                                        echo ', ';
                                    } else {
                                        echo $value . '.';
                                    }
                                }
                            }
                        @endphp {{ $item->units }}</td>
                    </tr>
                @endif
            @endforeach


        </table>
    </div>
    <div class="col-md-3 bg-white pt-5">
        <div class="card-body p-5 border">
            <p class="text-justify" style="text-align: justify">Our system allows you send and recieve messages to
                product suppliers. Press the <b>Chat with supplier</b> button to start a coversation with supplier of
                this product.
            </p>

            @if ($is_logged_in)
                <a href="{{ $message_link }}"
                    class="btn  btn-danger btn-lg rounded-0 d-block btn-active-light-primary"><i
                        class="las la-comment-dots fs-2 me-2"></i>
                    {{ $message_text }}</a>
            @else
                <a href="{{ url('register') }}"
                    class="btn  btn-danger btn-lg rounded-0 d-block btn-active-light-primary"><i
                        class="las la-comment-dots fs-2 me-2"></i>
                    CHAT WITH SUPPLIER</a>
            @endif
            <div class="my-5"></div>

            <a data-bs-toggle="modal" data-bs-target="#kt_modal_1"
                class="btn btn-block btn-outline btn-outline-dashed btn-outline-danger btn-active-light-primary btn-lg rounded-0 d-block">
                Show Phone Number
            </a>
        </div>

        <div class="card-body p-5 mt-5 border">
            <h2>About the supplier</h2>
            <div class="row">
                <div class="col-3">
                    <a href="javascript:;">
                        <img class="img-fluid rounded-circle" src="{{ url('no_image.jpg') }}" alt="">
                    </a>
                </div>
                <div class="col-9 p-0 m-0 pb-1">
                    <p class="text-dark p-0 m-0 mt-2"><a href="javascript:;"
                            class="text-dark  fs-5 text-hover-primary text-capitalize"
                            style="line-height: 1.5rem;">{{ $pro->seller_name }}</a></p>
                    <p class="text-gray-600 "><a href="javascript:;" class="text-gray-600  fs-5 text-hover-primary"
                            style="line-height: .9rem;">{{ $pro->location->get_name() }}</a>
                    </p>
                </div>
            </div>
            <table class="table table-striped table-sm">
                <tbody>
                    <tr>
                        <td class="ps-4 pt-4 pb-0">
                            <p class="fs-4">A member since</p>
                        </td>
                        <td class="ps-4 pt-4 pb-0">
                            <p class="fs-4"><span class="text-primary">11 June, 2022</span></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- <div class="card shadow-sm mt-5 card-p-3">
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item active bg-dark border-dark">Stay safe!</li>
                    <li class="list-group-item">Avoid offers that look unrealistic</li>
                    <li class="list-group-item">Chat with seller to clarify item details</li>
                    <li class="list-group-item">Meet in a safe &amp; public place</li>
                    <li class="list-group-item">Check the item before buying it</li>
                    <li class="list-group-item">Don’t pay in advance</li>
                    <li class="list-group-item">
                        <button type="button" class="btn btn-sm btn-light-dark ">
                            See all safety tips
                        </button>
                    </li>
                </ul>
            </div>

        </div> --}}

    </div>


</div>

<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact {{ $pro->seller_name }}</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span class="svg-icon svg-icon-2x"></span>
                </div>
            </div>

            <div class="modal-body">
                <p>{{ $pro->seller_phone }}</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="row bg-white mt-5 py-5">
    <div class="col-12">
        <h2>Description</h2>
        {!! $description !!}
    </div>
</div>




<div class="row p-0 ">
    <div class="col-12 p-0 m-0">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-primary">
                <h3 class="card-title fs-1 text-white">You may also like</h3>
                <div class="card-toolbar">
                    <a href="{{ url('/product-listing') }}" type="button" class="btn btn-sm btn-light-primary">
                        See all
                    </a>
                </div>
            </div>

            <div class="card-body">


                <div class="row mt-2">
                    @foreach ($recomended as $item)
                        <div class="col-6 col-md-2">
                            @include('metro.components.product-item', [
                                'item' => $item,
                            ])
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>



<style>
    .fslightbox-absoluted {
        background-color: #414E4E;
    }
</style>
@endsection
