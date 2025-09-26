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
                                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
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
                        <div class="text-center py-8">
                            <div class="text-gray-500 text-lg">No downloads available</div>
                            <p class="text-gray-400 mt-2">Purchase digital products to access downloads here.</p>
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