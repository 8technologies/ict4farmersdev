<?php
$isSuccess = false;
$hasProduct = false;
$hasUser = false;
use App\Models\Product;
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'success') {
        $isSuccess = true;
    }
}
$_subject = '';
$_customer_name = '';
$_customer_email = '';
$_customer_phone = '';
$temp_pro = null;
if (isset($_GET['product_id'])) {
    $temp_pro = Product::find($_GET['product_id']);
    if ($temp_pro != null) {
        $hasProduct = true;
        $_subject = 'I am interested in ' . $temp_pro->name;
    }
}
$u = Admin::user();
if ($u != null) {
    $_customer_name = $u->name;
    $_customer_email = $u->email;
    $_customer_phone = $u->phone_number;
    $hasUser = true;
}
$_subject = old('subject') != null ? old('subject') : $_subject;
$_customer_name = old('name') != null ? old('name') : $_customer_name;
$_customer_email = old('email') != null ? old('email') : $_customer_email;
$_customer_phone = old('phone') != null ? old('phone') : $_customer_phone;
?><div class="row bg-white mt-8 p-10"
    style="background-image: url(https://www.micstatic.com/mic-search/img/home-2019/easy-sourcing.jpg?_v=1655724759401);     background-size:     cover;
        background-repeat:   no-repeat;
        background-position: center center; height: 38rem; ">
    <div class="d-none d-md-block col-5  fw-bold fs-3 py-1 m-0 px-3 text-gray-900">
        <h2 class="h1 display-4 mb-4">EASY SOURCING</h2>
        <p>{{ env('APP_NAME') }} is Uganda's largest online Farmers marketplace, connecting buyers with farmers.</p>
        <p>One request, multiple quotes</p>
        <p>Verified suppliers matching</p>
        <p>Quotes comparison and sample request</p>
    </div>
    <div class="d-none d-md-block col-1"></div>
    <div class="col-12 col-md-6 bg-white p-5 p-md-10">
        @if ($isSuccess)
            <div class="text-center">
                <div class="alert alert-success text-center">Your inquiry has been received! We will get back to you via
                    email very
                    soon.</div>
                <br>
                <a href="{{ url('product-listing') }}" class="btn btn-primary">Browse Our Shop</a>
                <hr>
                {{-- download our app button --}}
                <a target="_blank"
                    href="{{ 'https://play.google.com/store/apps/details?id=net.eighttechnologes.ict4farmers&hl=en&gl=US' }}"
                    class="btn btn-primary">Download Our Mobile App</a>
            </div>
        @else
            <h2 class="ps-0  display-6 fw-normal">Tell us what you need</h2>
            <form action="{{ url('inquiry') }}" method="POST">
                {{-- hidden csrf token --}}
                @csrf

                @if ($hasUser)
                    <input type="hidden" name="customer_id" value="{{ $u->id }}">
                @endif
                {{-- 
subject
message
response
status
	
 
    --}}
                <div class="form-group">
                    <input type="text" name="subject" class="form-control border border-primary"
                        placeholder="Subject" value="{{ $_subject }}">
                    {{-- error message --}}
                    @error('subject')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mt-4">
                    <textarea name="details" required placeholder="Message Details..." id="data"
                        class="form-control border border-primary " rows="3">{{ old('details') }}</textarea>
                    {{-- error message --}}
                    @error('details')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                {{-- customer_phone --}}
                <div class="form-group mt-4">
                    <input type="text" name="phone" required class="form-control border border-primary"
                        placeholder="Phone number" value="{{ $_customer_phone }}">
                    {{-- error message --}}
                    @error('phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group  mt-4">
                            <input type="email" name="email" required class="form-control border border-primary"
                                placeholder="Email address" value="{{ $_customer_email }}">
                            {{-- error message --}}
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 mt-4">
                        <div class="form-group">
                            <input type="text" name="name" required class="form-control border border-primary"
                                placeholder="Full name" value="{{ $_customer_name }}">
                            {{-- error message --}}
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <button type="submit" class="border border-primary btn btn-primary mt-3">Post Your Request</button>
            </form>
        @endif


    </div>
</div>
