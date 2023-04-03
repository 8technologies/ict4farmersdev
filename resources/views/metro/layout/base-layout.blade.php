<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.app_slogan') }}</title>
    <meta charset="utf-8" />
    <meta name="description" content="{{ config('app.app_slogan') }}" />
    <meta name="keywords" content="{{ config('app.app_slogan') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title"
        content="ICT 4 FARMERS - Making ICT work for farmers" />
    <meta property="og:url" content="{{url('assets/images/favicon.png') }}" />
    <meta property="og:site_name" content="ICT 4 FARMERS | Making ICT work for farmers" />
    <link rel="canonical" href="<?= url('/') ?>" />
    <link rel="shortcut icon" href="{{url('assets/images/favicon.png') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" async />
    <link href="{{ url('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" async />

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PNLCGK3S63"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
    
        gtag('config', 'G-PNLCGK3S63');
    </script>

    
    @yield('header')

    <style>
        .my-active-menu {
            border-bottom: var(--bs-blue) 5px solid;
            background-color: rgb(235, 235, 235);
        }

        .menu-link {
            border-radius: 0px !important;
        }

        .product:hover {
            margin: 2px;
            -webkit-box-shadow: 0px 0px 6px 2px #A6A6A6;
            box-shadow: 0px 0px 6px 2px #A6A6A6;
        }

        img.lazy {
            /* width: 700px;
            height: 467px;
            display: block;
 
            background-image: url('images/loading.gif');
            background-repeat: no-repeat;
            background-position: 50% 50%; */
        }
    </style>
</head>

@yield('base-content')


<script src="{{ url('assets/js/scripts.bundle.js') }}"></script>
<script>
    $(function() {
        $('.lazy').Lazy({
            // your configuration goes here
            scrollDirection: 'vertical',
            placeholder: '{{ url('no_image.jpg') }}',
            effect: 'fadeIn',
            visibleOnly: true,
            onError: function(element) {
                console.log('error loading ' + element.data('src'));
            }
        });
    });
</script>

<!--Start of Tawk.to Script-->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {},
        Tawk_LoadStart = new Date();
    (function() {
        var s1 = document.createElement("script"),
            s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/62ee7a3437898912e961972f/1g9poqoh3';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>
<!--End of Tawk.to Script-->
@yield('footer')
@yield('footer-2')

</html>
