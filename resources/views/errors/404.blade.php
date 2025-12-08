<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-blue-600 mb-2 animate-pulse">404</h1>
                <div class="h-1 w-24 bg-blue-600 mx-auto rounded-full"></div>
            </div>

            <!-- Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg">
                    <i class="fas fa-search text-6xl text-blue-500"></i>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h2>
                <p class="text-lg text-gray-600 mb-2">
                    Oops! The page you're looking for doesn't exist.
                </p>
                <p class="text-gray-500">
                    It might have been moved or deleted, or you may have mistyped the URL.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="history.back()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-md hover:shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                    <span>Go Back</span>
                </button>
                <a href="/"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg">
                    <i class="fas fa-home"></i>
                    <span>Go to Home</span>
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-12 p-6 bg-white rounded-lg shadow-md">
                <p class="text-sm text-gray-600">
                    If you believe this is a mistake, please contact support.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
