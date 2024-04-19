<?php
use App\Models\Banner;
$main_item = new Banner();

$_things = ['Baby Clothes', 'Sports Wear', 'Belt', 'Gloves', 'Sweaters', 'Hoodies', 'Bikini', 'T-Shirt', 'Swim wear', 'Jacket', 'Dresses', 'Pants & Trousers', 'Winter Apparel'];
if (isset($items[0])) {
    $main_item = $items[0];
    unset($items[0]);
}

?><div class="row bg-white mt-8">
    <div class=" d-md-block col-12 col-md-3 p-5"
        style="background-image: url({{ url('/public/storage').'/'.$main_item->image }});     background-size:     cover;
    background-repeat:   no-repeat;
    background-position: center center; height: 32rem; ">
        <h2 class="ps-0  display-5 fw-bolder">{{ $main_item->name }}</h2>
        <a class="btn btn-primary btn-sm mt-5" href="{{ $main_item->link }}">{{ $main_item->sub_title }}</a>
    </div>
    <div class="col-12 col-md-9">
        <div class="row">
            @foreach ($items as $item)
                <a href="{{ $item->link }}" class="col-6 col-md-3 border border-secondary"
                    style="background-image: url({{ url('/public/storage').'/'.$item->image }});     background-size:     cover;
            background-repeat:   no-repeat;
            background-position: center center; height: 16rem; ">
                    <div class="row">
                        <div class="col-md-8">
                            <h2 class="ps-4 pt-4    fw-normal text-gray-700" style="font-size: 1.5rem">
                                {{ $item->name }}</h2>
                        </div>
                    </div>
                </a>
            @endforeach

        </div>
    </div>
</div>
