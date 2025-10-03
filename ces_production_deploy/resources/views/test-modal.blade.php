<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-4">Modal Test Page</h1>
        
        <button 
            x-data=""
            x-on:click="$dispatch('open-modal', 'test-modal')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            Open Test Modal
        </button>

        <x-modal name="test-modal" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Test Modal</h2>
                <p class="mt-1 text-sm text-gray-600">
                    This is a test modal to reproduce the setProperty error.
                </p>
                <div class="mt-6 flex justify-end">
                    <button 
                        x-on:click="$dispatch('close')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Close
                    </button>
                </div>
            </div>
        </x-modal>
    </div>
</body>
</html>