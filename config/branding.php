<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Branding
    |--------------------------------------------------------------------------
    |
    | This file contains branding-related configuration for your application.
    | You can customize the application name, logo, colors, and other
    | branding elements here.
    |
    */

    // Application name (overrides the APP_NAME in .env)
    'app_name' => env('BRANDING_APP_NAME', env('APP_NAME', 'Task Management')),
    
    // Short application name (used in places where space is limited)
    'app_short_name' => env('BRANDING_APP_SHORT_NAME', 'TM'),
    
    // Application tagline
    'tagline' => env('BRANDING_TAGLINE', 'Manage your tasks efficiently'),
    
    // Primary color (hex code)
    'primary_color' => env('BRANDING_PRIMARY_COLOR', '#3b82f6'),
    
    // Secondary color (hex code)
    'secondary_color' => env('BRANDING_SECONDARY_COLOR', '#10b981'),
    
    // Logo path (relative to public directory)
    'logo_path' => env('BRANDING_LOGO_PATH', 'assets/images/logo.png'),
    
    // Favicon path (relative to public directory)
    'favicon_path' => env('BRANDING_FAVICON_PATH', 'favicon.ico'),
    
    // Footer text
    'footer_text' => env('BRANDING_FOOTER_TEXT', '© ' . date('Y') . ' ' . env('APP_NAME', 'Task Management')),

];
