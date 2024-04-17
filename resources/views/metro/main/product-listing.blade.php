@extends('metro.layout.layout-main')
<?php
use App\Models\Product;
use App\Models\Category;
use App\Models\Utils;

$top_categories = Category::get_top_categories(8);
$page_title = 'Popular items';

$products = [];
$conds = [];
$per_page = 24;

$seg = request()->segment(1);
$cat = Category::where('slug', $seg)->first();
if ($cat != null) {
    if ($cat->id != null) {
        $conds['category_id'] = $cat->id;
        $page_title = $cat->name;
    }
}
$all_cats = Category::all();

//ifempty, product-listing.blade

if (empty($products)) {
    $products = Product::where([])->orderBy('id', 'desc')->paginate($per_page)->withQueryString();
}

/*  for ($i=1; $i < 100; $i++) { 
    $p['source'] = 'public/storage/'.$i.'.jpg'; 
    $p['target'] = 'public/storage/'.$i.'_thumb.jpg'; 
    Utils::create_thumbail($p);
} 

$x = 5;
foreach (Product::all() as $key => $p) {
    
     $x++;
    $i = $x%98;
    $i = $i - 2;
    $d['src'] = $i.'.jpg';
    $d['thumbnail'] = $i.'_thumb.jpg';
    $d['user_id'] = 1;
    $p->thumbnail = json_encode($d);
    
    
}  */

?>
@section('main-content')
    <div class="row mt-5">
        <div class="col-md-3 bg-white p-5 border border-left-0 border-top-0 border-bottom-0  border-secondary">
            <h2 class="m-0 fs-3 fw-bold">Browse by category</h2>
            <div class="accordion accordion-icon-toggle py-1" id="kt_accordion_2">
                @foreach ($all_cats as $cat)
                    <?php
                    if ($cat->parent > 0) {
                        continue;
                    }
                    $kids = $cat->kids;
                    $collapsed = ' collapsed ';
                    $aria_expanded = 'false';
                    $show = '';
                    foreach ($kids as $k) {
                        if ($k->slug == $seg) {
                            $collapsed = '';
                            $aria_expanded = 'true';
                            $show = ' show ';
                        }
                    }
                    
                    ?>
                    <div class="mb-0">
                        <div class="accordion-header py-1 d-flex {{ $collapsed }}" data-bs-toggle="collapse"
                            data-bs-target="#accordion_item_{{ $cat->id }}" aria-expanded="{{ $aria_expanded }}">
                            <span class="accordion-icon">
                                <i class="las la-angle-double-right fs-1"></i>
                            </span>
                            <h3 class="fs-4 fw-normal m-0 text-gray-800">{{ $cat->name }}</h3>
                        </div>
                        <div id="accordion_item_{{ $cat->id }}" class="fs-6 collapse  ps-10 {{ $show }}"
                            data-bs-parent="#kt_accordion_2">
                            @foreach ($kids as $kid)
                                @php
                                    $active = ' text-gray-600  ';
                                    if ($kid->slug == $seg) {
                                        $active = ' text-primary ';
                                    }
                                @endphp
                                <a href="{{ url($kid->slug) }}">
                                    <h4 class="fs-5 fw-normal m-0 my-1 text-hover-primary {{ $active }}">
                                        {{ $kid->name }}
                                    </h4>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
        <div class="col-md-9 bg-white">
            <div class="row">
                <div
                    class="d-flex d-flex align-items-stretch justify-content-between pt-4
                border border-left-0 border-right-0 border-top-0 border-bottom-1  border-secondary
                ">
                    <h2 class="m-0 mb-4 h1 fw-bold">{{ $page_title }}</h2>
                    <a class="m-0 d-block mt-0 fs-4 text-hover-primary text-dark" href="{{ url('product-listing') }}">Clear
                        filter</a>
                </div>

                <div class="row pt-0 m-0 p-0">
                    @foreach ($products as $item)
                        <div class="col-6 col-md-3 border border-1 p-2">
                            @include('metro.components.product-item', [
                                'item' => $item,
                            ])
                        </div>
                    @endforeach
                </div>

                <div class="row mb-5">
                    <div class="col-lg-12">
                        <div class="footer-pagection border-0">
                            @if ($products != null)
                                <p class="page-info">Showing {{ $products->count() }} of {{ $products->total() }}
                                    Results</p>
                                {{ $products->onEachSide(2)->links('main.pagination') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
