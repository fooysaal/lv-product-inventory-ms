<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-red-50 to-orange-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-red-600 mb-2 animate-pulse">500</h1>
                <div class="h-1 w-24 bg-red-600 mx-auto rounded-full"></div>
            </div>

            <!-- Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-500"></i>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Internal Server Error</h2>
                <p class="text-lg text-gray-600 mb-2">
                    Oops! Something went wrong on our end.
                </p>
                <p class="text-gray-500">
                    We're working to fix the problem. Please try again later.
                </p>
            </div>

            @if (config('app.debug') && isset($exception))
                <!-- Error Details (Development Mode) -->
                <div class="mb-8 p-4 bg-white rounded-lg shadow-md text-left">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-bug text-red-500"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Error Details</h3>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded p-3">
                        <p class="text-sm text-red-800 font-mono break-all">{{ $exception->getMessage() }}</p>
                        @if ($exception->getFile())
                            <p class="text-xs text-gray-600 mt-2">
                                File: {{ $exception->getFile() }}:{{ $exception->getLine() }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="location.reload()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md hover:shadow-lg">
                    <i class="fas fa-redo"></i>
                    <span>Try Again</span>
                </button>
                <a href="/"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-md hover:shadow-lg">
                    <i class="fas fa-home"></i>
                    <span>Go to Home</span>
                </a>
            </div>

            <!-- Help Section -->
            <div class="mt-12 p-6 bg-white rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Need Help?</h3>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-envelope text-red-500"></i>
                        <span>Contact Support</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-book text-red-500"></i>
                        <span>View Documentation</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-life-ring text-red-500"></i>
                        <span>Get Help</span>
                    </div>
                </div>
            </div>

            <!-- Status Info -->
            <div class="mt-8 text-sm text-gray-500">
                <p>Error occurred at: {{ now()->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>
</body>

</html>
