<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Checkout - {{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Test Checkout</h1>
            <p class="text-gray-600">Bu bir test checkout sayfasıdır</p>
        </div>
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">{{ $title }}</h2>
            <div class="space-y-2 text-sm text-gray-600">
                <p><strong>Type:</strong> {{ ucfirst($type) }}</p>
                <p><strong>ID:</strong> {{ $id }}</p>
                <p><strong>Price:</strong> {{ $price }} TL</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Paddle Price ID:</strong> {{ $paddlePriceId }}</p>
            </div>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Checkout Başarılı!</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Test checkout URL'i başarıyla oluşturuldu.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="space-y-3">
            <button onclick="simulateSuccess()" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                Simulate Successful Payment
            </button>
            <button onclick="simulateCancel()" class="w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                Simulate Cancel
            </button>
            <button onclick="goBack()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Go Back
            </button>
        </div>
    </div>

    <script>
        function simulateSuccess() {
            // Redirect to success page
            window.location.href = '/success?type={{ $type }}&id={{ $id }}&title=' + encodeURIComponent('{{ $title }}') + '&price={{ $price }}&email={{ $email }}';
        }
        
        function simulateCancel() {
            // Redirect to cancel page
            window.location.href = '/cancel?type={{ $type }}&id={{ $id }}&title=' + encodeURIComponent('{{ $title }}') + '&price={{ $price }}&email={{ $email }}';
        }
        
        function goBack() {
            // Go back to the previous page
            window.history.back();
        }
    </script>
</body>
</html>
