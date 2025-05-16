<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-bind:class="{ 'dark': darkMode }">

<head>
    @unless(request()->is('api/*'))
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endunless
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="description" content="@yield('description', 'Laravel Developer specializing in PHP, MySQL, and PSD-to-Laravel conversions. Portfolio includes live sites built with Laravel, WordPress, and custom PHP. Open to full-time remote roles.')">
    <meta name="keywords" content="@yield('keywords', 'Laravel Developer, PHP, MySQL, PSD to Laravel, WordPress, LAMP stack, remote developer, Coalition Technologies hiring')">
    <meta name="language" content="English">
    <meta name="author" content="Bobi Gunardi">
    <meta name="robots" content="index, follow">
    <!-- Base URL for JavaScript -->
    <script>
        window.AppConfig = {
            baseUrl: "{{ rtrim(config('app.url'), '/') }}",
            currentUrl: "{{ url()->current() }}",
            assetUrl: "{{ rtrim(asset('/'), '/') }}"
        };
    </script>
    <!-- In your <head> -->
<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <title>{{ config('app.name', 'Task Management') }} - @yield('title', 'Home')</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <style>
    [x-cloak] { display: none !important; }
    .fade-in {
        animation: fadeIn 0.2s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>
    @stack('styles')
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="{{ asset('assets/css/overide.css') }}" rel="stylesheet">
    <!-- Scripts -->

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- <script src="{{ asset('assets/js/app.js') }}" defer></script> -->

    @stack('styles')
</head>
<body class="@yield('body-class', 'font-sans min-h-screen flex flex-col antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-200')">
    <div class="@if(!request()->routeIs('login')) h-100 @endif">

        @unless(request()->routeIs('login'))
        @include('layouts.navbar')
        @endunless

        <main class="flex-grow" role="main">
            @hasSection('full-width')
            @yield('content')
            @else
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
            @endif
        </main>
        
        @unless(request()->routeIs('login'))
        @include('layouts.footer')
        @endunless
    </div>
</body>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
<!-- Sortable.js -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Custom Toastr styles for better visibility
    const style = document.createElement('style');
    style.textContent = `
        /* Base toast styling */
        #toast-container > div {
            padding: 15px 15px 15px 50px !important;
            width: 350px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            line-height: 1.4 !important;
            opacity: 1 !important;
        }
        
        /* Warning toast specific */
        .toast-warning {
            background-color: #fff3cd !important;
            color: #856404 !important;
            border-left: 6px solid #ffc107 !important;
        }
        
        .toast-warning .toast-title {
            font-weight: 600 !important;
            font-size: 16px !important;
            margin-bottom: 5px !important;
            color: #856404 !important;
        }
        
        .toast-warning .toast-message {
            color: #856404 !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
        }
        
        .toast-warning .toast-close-button {
            color: #856404 !important;
            opacity: 0.8 !important;
            font-size: 18px !important;
            font-weight: bold !important;
            text-shadow: none !important;
        }
        
        .toast-warning .toast-close-button:hover {
            opacity: 1 !important;
            color: #000 !important;
        }
        
        .toast-warning .toast-progress {
            background: rgba(133, 100, 4, 0.3) !important;
            height: 3px !important;
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .toast-warning {
                background-color: #332701 !important;
                color: #ffd43b !important;
            }
            .toast-warning .toast-title,
            .toast-warning .toast-message {
                color: #ffd43b !important;
            }
        }`;
    document.head.appendChild(style);

    // Navbar scroll effect
    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.querySelector('nav');
        const scrollIndicator = document.getElementById('scroll-indicator');
        let lastScroll = 0;

        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            // Show/hide scroll indicator
            if (scrollIndicator) {
                scrollIndicator.style.opacity = currentScroll > 10 ? '1' : '0';
            }
            
            // Add/remove shadow on scroll
            if (nav) {
                if (currentScroll > 10) {
                    nav.classList.add('shadow-md');
                    nav.classList.remove('hover:shadow-md');
                } else {
                    nav.classList.remove('shadow-md');
                    nav.classList.add('hover:shadow-md');
                }
            }
            
            lastScroll = currentScroll;
        });
    });
</script>

<!-- Pusher JS -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<!-- jQuery BlockUI -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>

<!-- Initialize URL service SEO -->
<script src="{{ asset('assets/js/url-service.js') }}"></script>

@stack('scripts')
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Bobi Gunardi",
        "alternateName": "Your Name",
        "description": "Laravel Developer with expertise in PHP, MySQL, Laravel, AI Integration and JavaScript.",
        "url": "https://mycv-blue-three.vercel.app/",
        "jobTitle": "Laravel Developer",
        "skills": "PHP, MySQL, Laravel, JavaScript"
    }
</script>
<script>

    
    document.addEventListener('DOMContentLoaded', function() {
        const {
            urlService
        } = window;

        // Example for AJAX navigation with proper SEO considerations
        document.querySelectorAll('a[data-ajax-load]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const targetUrl = this.getAttribute('href');
                const fullUrl = targetUrl.startsWith('http') ?
                    targetUrl :
                    urlService.url(targetUrl);

                // Update browser history for SEO and back button
                window.history.pushState({
                    path: fullUrl
                }, '', fullUrl);

                // Load content via AJAX
                fetch(fullUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('content').innerHTML = html;
                    });
            });
        });
    });
    // Base URL and CSRF Token Setup
    const baseURL = "{{ url('/') }}";

    // Apply dark mode based on localStorage or system preference
    const darkPref = localStorage.getItem('darkMode');
    const shouldUseDark = darkPref === 'true' ||
        (darkPref === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', shouldUseDark);
    
</script>
<!-- Flash Messages -->
@if(session('success'))
<script>
    toastr.success('{{ session('success') }}');
</script>
@endif

@if(session('error'))
<script>
    toastr.error('{{ session('error') }}');
</script>
@endif

<!-- Toast Notifications Container -->
<div id="toast-container" class="fixed bottom-4 right-4 z-50 w-80 space-y-2"></div>

</body>

</html>