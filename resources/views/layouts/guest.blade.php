<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO') }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc7fb',
                            400: '#36abf7',
                            500: '#0c8fe9',
                            600: '#0171c7',
                            700: '#025aa2',
                            800: '#064d85',
                            900: '#0b416e',
                            950: '#072949',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #1e293b, #0f172a, #020617);
        }
    </style>
</head>
<body class="h-full flex flex-col justify-center items-center p-4 text-slate-100 antialiased selection:bg-brand-500 selection:text-white">
    <div class="w-full max-w-md">
        @yield('content')
    </div>
</body>
</html>
