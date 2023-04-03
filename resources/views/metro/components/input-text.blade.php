@php
    $_label = isset($label) && !empty($label) ? $label : '';
    $_hint = isset($hint) && !empty($hint) ? $hint : '';
    $_required = isset($required) && !empty($required) ? $required : '';
    $_attributes = isset($attributes) && !empty($attributes) ? $attributes : [];
    $_value = isset($value) && !empty($value) ? $value : '';
    $_classes = isset($classes) && !empty($classes) ? $classes : '';
    
    $_name = $_attributes['name'];
    $_id = $_name;
    
    if (old($_name) != null && strlen(old($_name)) > 0) {
        $_value = old($_name);
    }
    
@endphp
<div class="form-group">

    @if (!empty($label))
        <label for="{{ $_id }}"
            class="form-label fw-normal text-dark fs-3  p-0 m-0 {{ $_required }}">{{ $_label }}</label>
    @endif
    {{-- form-control-solid --}}
    <input id="{{ $_id }}" class="form-control {{ $_classes }}"
        @foreach ($_attributes as $key => $value) {{ $key . '=' . $value }} @endforeach {{ $_required }}
        value="{{ $_value }}" />
        @if('password' == $_attributes['name'])
            <i class="fa fa-eye" style="margin-left: 375px; margin-top: -27px; cursor:pointer;" id = "togglePassword"></i>
        @elseif('password_confirmation' == $_attributes['name'])
            <i class="fa fa-eye" style="margin-left: 375px; margin-top: -27px; cursor:pointer;" id = "toggleConfirmPassword"></i>
        @endif
    @error($_name)
        <div class="text-danger mt-2">{{ $message }}</div>
    @enderror


    @if (!empty($_hint))
        <div class="text-muted mt-09">{{ $_hint }}</div>
    @endif

</div>
