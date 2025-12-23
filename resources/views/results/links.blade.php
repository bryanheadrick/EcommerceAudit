<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Links - {{ $audit->domain }}
                </h2>
            </div>
            <a href="{{ route('audits.show', $audit) }}" class="text-sm text-gray-600 hover:underline">
                Back to Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Total Links</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCount }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Broken Links</p>
                            <p class="text-3xl font-bold {{ $brokenCount > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">
                                {{ $brokenCount }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Success Rate</p>
                            <p class="text-3xl font-bold {{ $totalCount > 0 && (($totalCount - $brokenCount) / $totalCount * 100) >= 95 ? 'text-green-600' : 'text-yellow-600' }} mt-2">
                                {{ $totalCount > 0 ? number_format(($totalCount - $brokenCount) / $totalCount * 100, 1) : 0 }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('audits.results.links', $audit) }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="broken_only" value="1" {{ request('broken_only') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Show broken links only</span>
                                </label>
                            </div>

                            <div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search URLs..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Apply Filters
                            </button>
                            <a href="{{ route('audits.results.links', $audit) }}" class="text-sm text-gray-600 hover:underline">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Links List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $links->total() }} {{ Str::plural('Link', $links->total()) }}
                    </h3>

                    @if($links->isEmpty())
                        <p class="text-gray-500 text-center py-8">No links found.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($links as $link)
                                <div class="border-l-4 {{ $link->is_broken ? 'border-red-500 bg-red-50' : 'border-green-500 bg-green-50' }} p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <x-badge :color="$link->is_broken ? 'red' : 'green'">
                                                    {{ $link->is_broken ? 'Broken' : 'OK' }}
                                                </x-badge>
                                                <x-badge color="gray">
                                                    {{ $link->status_code ?? 'N/A' }}
                                                </x-badge>
                                            </div>

                                            <p class="text-sm font-medium text-gray-900 break-all">
                                                <a href="{{ $link->url }}" target="_blank" class="text-blue-600 hover:underline">
                                                    {{ $link->url }}
                                                </a>
                                            </p>

                                            @if($link->page)
                                                <p class="text-xs text-gray-500 mt-2">
                                                    Found on:
                                                    <a href="{{ $link->page->url }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ $link->page->url }}
                                                    </a>
                                                </p>
                                            @endif

                                            @if($link->error_message)
                                                <p class="text-sm text-red-700 mt-2">
                                                    Error: {{ $link->error_message }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $links->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
