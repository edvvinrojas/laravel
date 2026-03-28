<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CopyMart ERP')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        @layer components {
            .btn { @apply inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150; }
            .btn-primary { @apply btn bg-blue-600 text-white hover:bg-blue-700; }
            .form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
            .form-input { @apply w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
            .form-error { @apply text-xs text-red-600 mt-1; }
        }
    </style>
</head>
<body class="h-full flex items-center justify-center">
    @yield('content')
</body>
</html>
