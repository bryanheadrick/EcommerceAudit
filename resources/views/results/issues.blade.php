<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Issues - {{ $audit->domain }}
                </h2>
            </div>
            <a href="{{ route('audits.show', $audit) }}" class="text-sm text-gray-600 hover:underline">
                Back to Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('audits.results.issues', $audit) }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" id="category" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                            {{ ucfirst($category) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="severity" class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                                <select name="severity" id="severity" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                    <option value="">All Severities</option>
                                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                                </select>
                            </div>

                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search issues..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Apply Filters
                            </button>
                            <a href="{{ route('audits.results.issues', $audit) }}" class="text-sm text-gray-600 hover:underline">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Issues List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $issues->total() }} {{ Str::plural('Issue', $issues->total()) }} Found
                    </h3>

                    @if($issues->isEmpty())
                        <p class="text-gray-500 text-center py-8">No issues found matching your criteria.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($issues as $issue)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <x-badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'high' ? 'orange' : ($issue->severity === 'medium' ? 'yellow' : 'gray'))">
                                                    {{ ucfirst($issue->severity) }}
                                                </x-badge>
                                                <x-badge color="blue">
                                                    {{ ucfirst($issue->category) }}
                                                </x-badge>
                                            </div>

                                            <h4 class="text-lg font-semibold text-gray-900">{{ $issue->title }}</h4>
                                            <p class="text-sm text-gray-700 mt-2">{{ $issue->description }}</p>

                                            @if($issue->page)
                                                <p class="text-sm text-gray-500 mt-2">
                                                    <span class="font-medium">Page:</span>
                                                    <a href="{{ $issue->page->url }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ $issue->page->url }}
                                                    </a>
                                                </p>
                                            @endif

                                            <div class="mt-3 bg-blue-50 border-l-4 border-blue-400 p-3">
                                                <p class="text-sm font-medium text-blue-900">Recommendation:</p>
                                                <p class="text-sm text-blue-800 mt-1">{{ $issue->recommendation }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $issues->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
