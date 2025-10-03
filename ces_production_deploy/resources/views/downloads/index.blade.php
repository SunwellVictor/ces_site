<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Downloads') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($downloadGrants->count() > 0)
                        <div class="space-y-6">
                            @foreach($downloadsByProduct as $productTitle => $grants)
                                <div class="border rounded-lg p-4">
                                    <h3 class="text-lg font-semibold mb-4">{{ $productTitle }}</h3>
                                    
                                    <div class="space-y-3">
                                        @foreach($grants as $grant)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                                <div class="flex-1">
                                                    <div class="font-medium">{{ $grant->file->original_name }}</div>
                                                    <div class="text-sm text-gray-600">
                                                        {{ remaining_attempts($grant) }} of {{ $grant->max_downloads }} downloads remaining
                                                        @if($grant->expires_at)
                                                            • Expires {{ $grant->expires_at->format('M j, Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="flex space-x-2">
                                                    @if($grant->isValid() && remaining_attempts($grant) > 0)
                                                        <button 
                                                            onclick="generateDownloadToken('{{ $grant->id }}')"
                                                            class="bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none text-white font-bold py-2 px-4 rounded text-sm transition-colors duration-200"
                                                            aria-label="Download {{ $grant->file->name }} ({{ remaining_attempts($grant) }} downloads remaining)"
                                                            title="Download {{ $grant->file->name }}"
                                                        >
                                                            Download
                                                        </button>
                                                    @else
                                                        <span class="bg-gray-100 text-gray-500 px-4 py-2 rounded text-sm">
                                                            @if($grant->expires_at && $grant->expires_at < now())
                                                                Expired
                                                            @else
                                                                No downloads remaining
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $downloadGrants->links() }}
                        </div>
                    @else
                        <!-- Enhanced Empty State -->
                        <div class="text-center py-16">
                            <!-- Icon -->
                            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            
                            <!-- Title and Message -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">No downloads available</h3>
                            
                            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                                You don't have any downloadable files yet. Purchase digital products to access your downloads here.
                            </p>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                <a href="{{ route('products.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                    Browse Digital Products
                                </a>
                                <div class="text-sm text-gray-500">
                                    <a href="{{ route('account.orders') }}" class="text-blue-600 hover:text-blue-800">View your orders</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        async function generateDownloadToken(grantId) {
            try {
                const response = await fetch(`/downloads/${grantId}/token`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Open download in new window/tab
                    window.open(data.download_url, '_blank');
                } else {
                    alert(data.message || 'Failed to generate download link');
                }
            } catch (error) {
                console.error('Error generating download token:', error);
                alert('Failed to generate download link');
            }
        }
    </script>
</x-app-layout>