<?php 
header('Location: '.url());
die();
?>@php
use App\Models\Product;
use App\Models\Utils;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Chat;

$slug = request()->segment(1);
$pro = Product::where('slug', $slug)->firstOrFail();
if ($pro) {
    if (!$pro->owner) {
        dd('owner not found.');
    }
}
$products = [];
$conds['category_id'] = $pro->category->id;
$products = Product::where($conds)->paginate(4);

$images = $pro->get_images();
$attributes = json_decode($pro->attributes);
$url = $_SERVER['REQUEST_URI'];

$is_logged_in = false;

$user = Auth::user();
$message_link = '/login';
$message_text = 'Start converstion';
$message_link = 'https://play.google.com/store/apps/details?id=net.eighttechnologes.ict4farmers&hl=en&gl=US';
if ($user != null) {
    if (isset($user->id)) {
        $is_logged_in = true;
        if ($pro->user_id == $user->id) {
            $message_link = 'javascript:;';
            $message_text = 'This is your product.';
        } else {
            $chat_thred = Chat::get_chat_thread_id($user->id, $pro->user_id, $pro->id);
            $message_link = '/messages/' . $chat_thred;
        }
    }
}

@endphp
@extends('layouts.layout')




@section('content')
@section('title', $pro->name)
{{-- <link rel="stylesheet" href="{{ URL::asset('/assets/css/custom/ad-details.css') }}">
<link rel="stylesheet" href="{{ URL::asset('/assets/css/vendor/simple-lightbox.css') }}"> --}}


<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="pt-3">
                <div class="ad-details-slider-group ">
                    <div id="carouselExampleIndicators" class="carousel slide " data-ride="carousel">
                        <ol class="carousel-indicators">
                            <?php
                            $first_seen = false;
                            $active = '';
                            $counter_1 = -1;
                            ?>
                            @foreach ($images as $img)
                                @php
                                    $active = '';
                                    $counter_1++;
                                    if (!$first_seen) {
                                        $active = ' active ';
                                        $first_seen = true;
                                    }
                                @endphp
                                <li data-target="#carouselExampleIndicators  <?= $active ?> "
                                    data-slide-to="<?= $counter_1 ?>" class="active"></li>
                            @endforeach

                        </ol>

                        <?php $first_seen = false; ?>
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
                                    <a class="d-block w-100" href="#" href="#" data-toggle="modal"
                                        data-target=".image-modal">
                                        <img class="d-block w-100" src="{{ $img->thumbnail }}" alt="details"
                                            alt="First slide">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button"
                            data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button"
                            data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                    <div class="cross-vertical-badge ad-details-badge"><i class="fas fa-search-plus"></i><span>Click to
                            zoom</span></div>
                </div>
            </div>
            <div class="mt-2 mb-3">
                <h1 class="h3">{{ $pro->name }}</h1>
                <ul class="ad-details-specific">
                    <li>
                        <h6>Price:</h6>
                        <p>{{ config('app.currency') . " " }} {{ $pro->price }}
                            @if ($pro->fixed_price)
                                <small><i>Fixed price</i></small>
                            @else
                                <small><i>Negotiable</i></small>
                            @endif
                        </p>
                    </li>
                    <li>
                        <h6>published:</h6>
                        <p>{{ $pro->created_at }}</p>
                    </li>
                    <li>
                        <h6>location:</h6>

                    </li>
                    @foreach ($attributes as $item)
                        @if ($item->type == 'text' || $item->type == 'textarea')
                            <li>
                                <h6>{{ $item->name }}:</h6>
                                <p>{{ $item->value }} {{ $item->units }}</p>
                            </li>
                        @elseif($item->type == 'number')
                            <li>
                                <h6>{{ $item->name }}: </h6>
                                <p>{{ $item->value }} {{ $item->units }}</p>
                            </li>
                        @elseif($item->type == 'select')
                            <li>
                                <h6>{{ $item->name }}: </h6>
                                <p>{{ $item->value }} {{ $item->units }}</p>
                            </li>
                        @elseif($item->type == 'radio')
                            <li>
                                <h6>{{ $item->name }}: </h6>
                                <p>{{ $item->value }} {{ $item->units }}</p>
                            </li>
                        @elseif($item->type == 'checkbox')
                            <li>
                                <h6 class="mr-3">{{ $item->name }}: </h6>
                                <p> @php
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
                                @endphp {{ $item->units }}</p>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="mb-4">
                <div class="card-header">
                    <h5 class="card-title p-0 m-0 ">Description</h5>
                </div>
                <p class="ad-details-desc">{{ $pro->category->description }}</p>
            </div>
            {{-- <div class="" id="review">
                <div class="card-header p-0 m-0 pt-2">
                    <h5 class="card-title">reviews (@php
                        echo count($pro->reviews)
                        @endphp)</h5>
                </div>
                <div class="ad-details-review">
                    <ul class="review-list">

                        @foreach ($pro->reviews as $item)
                        <li class="review-item">
                            <div class="review-user">
                                <div class="review-head">
                                    <div class="review-profile">
                                        <a href="#" class="review-avatar"><img
                                                src="<?= URL::asset('assets/') ?>/images/avatar/03.jpg"
                                                alt="review"></a>
                                        <div class="review-meta">
                                            <h6><a href="#">{{$item->user->email}}
                                                    -</a><span>{{$item->created_at->diffForHumans()}}</span></h6>
                                            <ul>
                                                @php
                                                for ($i=0; $i < $item->rating; $i++) {
                                                    echo '<li><i class="fas fa-star active"></i></li>';
                                                    }
                                                    for ($i=0; $i < (5-$item->rating); $i++) {
                                                        echo '<li><i class="fas fa-star "></i></li>';
                                                        }
                                                        @endphp

                                                        <li>
                                                            <h5> - {{$item->reason}}</h5>
                                                        </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <p class="review-desc">{{$item->comment}}.</p>
                            </div>
                        </li>
                        @endforeach

                    </ul>
                    @guest
                    <div class="row justify-content-center">
                        <div class="col-12 ">
                            <div class="card dash-header-card pt-0">
                                <div class="card-body text-center">


                                    <h2 class="h3 text-center mt-0">Login Required</h2>

                                    <P class="text-left mb-2">You need to be loggedin in order to submit your review
                                        about this product.</P>
                                    <a href="register" class="btn btn-inline mt-4 mb-2 post-btn"><i
                                            class="fas fa-plus-circle"></i><span>CREATE ACCOUNT</span></a>
                                    <a href="login" class="btn btn-inline mt-4 mb-2 post-btn"><i
                                            class="fas fa-plus-circle"></i><span>LOGIN</span></a>


                                </div>
                            </div>
                        </div>
                    </div>

                    @endguest
                    @auth
                    <form method="post" action="{{$url}}" class="review-form">
                        @csrf
                        <input type="number" name="product_id" hidden value="{{$pro->id}}">

                        <div class="review-form-grid">
                            <div class="form-group"><input type="text" class="form-control" placeholder="Name">
                            </div>
                            <div class="form-group"><input type="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="form-group"><select name="reason" required class="form-control custom-select">
                                    <option></option>
                                    <option value="Qoute">Qoute</option>
                                    <option value="Product quality">product quality</option>
                                    <option value="Delivery system">delivery system</option>
                                    <option value="Payment issue">Payment issue</option>
                                </select></div>
                        </div>
                        <div class="star-rating">
                            <input value="5" type="radio" name="rating" id="star-1"><label for="star-1"></label>
                            <input value="4" type="radio" name="rating" id="star-2"><label for="star-2"></label>
                            <input value="3" type="radio" name="rating" id="star-3"><label for="star-3"></label>
                            <input value="2" type="radio" name="rating" id="star-4"><label for="star-4"></label>
                            <input value="1" type="radio" required name="rating" id="star-5"><label
                                for="star-5"></label>
                        </div>
                        <div class="form-group"><textarea required name="comment" class="form-control"
                                placeholder="Describe" rows="3"></textarea>
                        </div><button type="submit" class="btn btn-inline review-submit"><i
                                class="fas fa-tint"></i><span>drop your
                                review</span></button>
                    </form>

                    @endauth
                </div>
            </div> --}}
        </div>

        <div class="modal" id="number">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>Contact this Number</h4><button class="fas fa-times" data-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h3 class="modal-number">{{ $pro->user->phone_number }} </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-3">

            <div class="common-card p-0">
                <div class="border-bottom pl-3 pr-3 pt-3 pb-2">
                    <h5 class="card-title ">@php
                        echo config('app.currency');
                    @endphp {{ $pro->price }}</h5>
                </div>

                <div class="row pl-3 pt-2">
                    @php
                        $profile_pic = 'assets/images/avatar/03.jpg';
                        if ($pro->user != null) {
                            if ($pro->user->profile != null) {
                                if ($pro->user->profile_photo != null) {
                                    $imgs = json_decode($pro->user->profile_photo);
                                    if ($imgs->src) {
                                        $profile_pic = Utils::get_file_url($imgs->src);
                                    }
                                }
                            }
                        }
                        
                    @endphp
                    <div class="col-2">
                        <a href="/{{ $pro->user->username }}" class="author-img active">
                            <img height="50" width="50" class="rounded-circle" src="{{ $profile_pic }}"
                                alt="avatar"></a>
                    </div>
                    <div class="col">
                        <div class="author-meta">
                            <h4 class="p-0 m-0"><a class="title-1" href="/{{ $pro->user->username }}">
                                    {{ $pro->user->first_name }}
                                    {{ $pro->user->last_name }}
                                </a></h4>
                            <h5 class="subtitle-1 p-0 m-0">joined: {{ $pro->user->created_at->diffForHumans() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="row pl-3 pr-3 pt-2">
                    <div class="col-12 ">
                        <a href="{{ $message_link }}" class="common-card number p-2 pl-4 pr-4 bg-primary">
                            <h4 class="text-white">Send Message</h4><i class="fas text-white fa-envelope"></i>
                        </a>
                    </div>
                    @section('mobile-nav')
                        <div class="row  p-0 m-0 pt-2 pb-2 pl-1 pr-1" style="width: 100%">
                            <div class="col-6   p-1 m-0">
                                <a href="{{ $message_link }}" class="common-card number p-1  pl-3 pr-3 m-0 bg-primary">
                                    <h4 class="text-white">Chat</h4><i class="fas text-white fa-envelope"></i>
                                </a>
                            </div>
                            <div class="col-6 p-1 m-0" style="width: 100%">
                                <button data-toggle="modal" data-target="#number"
                                    class="common-card number p-0 m-0 p-1  pl-3 pr-3 bg-white border border-primary text-primary">
                                    <h4 class="text-primary p-0 m-0">
                                        Call
                                    </h4><i class="fas text-primary fa-phone"></i>
                                </button>
                            </div>
                        </div>

                    @endsection
                    <div class="col-12 ">
                        <button data-toggle="modal" data-target="#number"
                            class="common-card number p-2 pl-4 pr-4 bg-white border border-primary text-primary">
                            <h4 class="text-primary">
                                ({{ Str::substr($pro->user->phone_number, 0, 4) }}) Call Now
                            </h4><i class="fas text-primary fa-phone"></i>
                        </button>
                    </div>
                    <div class="col-12 ">
                        <a href="/{{ $pro->user->username }}"
                            class="common-card number p-2 pl-4 pr-4 bg-white  border-primary-dashed text-primary">
                            <h4 class="text-primary">
                                Visit Shop
                            </h4><i class="fas text-primary fa-eye"></i>
                        </a>
                    </div>
                </div>

            </div>




            <div class="common-card p-0">
                <div class="border-bottom pl-3 pr-3 pt-3 pb-2">
                    <h5 class="card-title ">safety tips</h5>
                </div>
                <div class="ad-details-safety pl-3 pl-3 pt-3 pb-3">
                    <p class="pt-0 pb-0">Check the item before you buy</p>
                    <p>Pay only after collecting item</p>
                    <p>Beware of unrealistic offers</p>
                    <p>Meet seller at a safe location</p>
                    <p>Do not make an abrupt decision</p>
                    <p>Be honest with the ad you post</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-4">
    <h5 class="card-title p-0 m-0 ">Similar items</h5>
    @foreach ($products as $item)
        <div class="col-md-8 pl-0 pr-0">
            <x-product2 :item="$item" />
        </div>
    @endforeach
</div>


<!-- Large modal -->
<div class="modal image-modal" style="background-color: #424E4E;" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">



            {{-- <button type="button" class=" close text-right p-3" style="background-color: #424E4E;"
                data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button> --}}

            <div class="ad-details-slider-group ">

                <div id="carouselExampleIndicators1" class="carousel slide " data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php
                        $first_seen = false;
                        $active = '';
                        $counter_1 = -1;
                        ?>
                        @foreach ($images as $img)
                            @php
                                $active = '';
                                $counter_1++;
                                if (!$first_seen) {
                                    $active = ' active ';
                                    $first_seen = true;
                                }
                            @endphp
                            <li data-target="#carouselExampleIndicators1  <?= $active ?> "
                                data-slide-to="<?= $counter_1 ?>" class="active"></li>
                        @endforeach

                    </ol>

                    <?php $first_seen = false; ?>
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
                                <img class="d-block w-100" src="{{ $img->src }}" alt="details" alt="First slide">
                            </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#carouselExampleIndicators1" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleIndicators1" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>


        </div>
    </div>
</div>


@endsection


<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        let $gallery = new SimpleLightbox('.slider-arrow a', {});
    });
</script>
