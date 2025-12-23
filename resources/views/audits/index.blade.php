<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Audits') }}
            </h2>
            <a href="{{ route('audits.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                New Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6">
                    <x-alert type="success">
                        {{ session('success') }}
                    </x-alert>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($audits->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500 mb-4">No audits yet.</p>
                            <a href="{{ route('audits.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Create Your First Audit
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($audits as $audit)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <a href="{{ route('audits.show', $audit) }}" class="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                                {{ $audit->domain }}
                                            </a>
                                            <p class="text-sm text-gray-500 mt-1">{{ $audit->url }}</p>

                                            <div class="mt-3 flex items-center gap-4">
                                                <x-badge :color="$audit->status === 'completed' ? 'green' : ($audit->isProcessing() ? 'blue' : ($audit->status === 'failed' ? 'red' : 'gray'))">
                                                    {{ ucfirst($audit->status) }}
                                                </x-badge>

                                                <span class="text-sm text-gray-500">
                                                    {{ $audit->pages_crawled }} {{ Str::plural('page', $audit->pages_crawled) }}
                                                </span>

                                                <span class="text-sm text-gray-500">
                                                    Created {{ $audit->created_at->diffForHumans() }}
                                                </span>

                                                @if($audit->completed_at)
                                                    <span class="text-sm text-gray-500">
                                                        Completed {{ $audit->completed_at->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($audit->score !== null)
                                            <div class="flex-shrink-0 ml-4">
                                                <div class="text-center">
                                                    <div class="text-4xl font-bold {{ $audit->score >= 80 ? 'text-green-600' : ($audit->score >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                        {{ $audit->score }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">Score</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-4 flex items-center gap-2">
                                        <a href="{{ route('audits.show', $audit) }}" class="text-sm text-blue-600 hover:underline">
                                            View Details
                                        </a>

                                        @if($audit->isCompleted())
                                            <span class="text-gray-300">•</span>
                                            <a href="{{ route('audits.report.pdf', $audit) }}" class="text-sm text-blue-600 hover:underline">
                                                Download Report
                                            </a>
                                        @endif

                                        @if($audit->isProcessing())
                                            <span class="text-gray-300">•</span>
                                            <form action="{{ route('audits.cancel', $audit) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-red-600 hover:underline">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif

                                        @if($audit->isCompleted() || $audit->isFailed())
                                            <span class="text-gray-300">•</span>
                                            <form action="{{ route('audits.restart', $audit) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-blue-600 hover:underline">
                                                    Re-run Audit
                                                </button>
                                            </form>
                                        @endif

                                        <span class="text-gray-300">•</span>
                                        <form action="{{ route('audits.destroy', $audit) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this audit?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $audits->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
