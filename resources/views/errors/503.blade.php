<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Unavailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-yellow-50 to-orange-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-yellow-600 mb-2 animate-pulse">503</h1>
                <div class="h-1 w-24 bg-yellow-600 mx-auto rounded-full"></div>
            </div>

            <!-- Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg">
                    <i class="fas fa-tools text-6xl text-yellow-500"></i>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Service Unavailable</h2>
                <p class="text-lg text-gray-600 mb-2">
                    We're currently performing maintenance.
                </p>
                <p class="text-gray-500">
                    We'll be back online shortly. Thank you for your patience.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="location.reload()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors shadow-md hover:shadow-lg">
                    <i class="fas fa-redo"></i>
                    <span>Try Again</span>
                </button>
            </div>

            <!-- Status Info -->
            <div class="mt-12 p-6 bg-white rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">What's Happening?</h3>
                <p class="text-sm text-gray-600">
                    We're performing scheduled maintenance to improve our services.
                    This should only take a few minutes.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
