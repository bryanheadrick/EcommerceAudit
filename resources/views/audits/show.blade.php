<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $audit->domain }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $audit->url }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($audit->isCompleted())
                    <a href="{{ route('audits.report.pdf', $audit) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Download Report
                    </a>
                @endif
                <a href="{{ route('audits.index') }}" class="text-sm text-gray-600 hover:underline">
                    Back to Audits
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif

            @if(session('error'))
                <x-alert type="error">{{ session('error') }}</x-alert>
            @endif

            <!-- Status & Score Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" @if($audit->isProcessing()) x-data="{ refreshInterval: null }" x-init="refreshInterval = setInterval(() => { window.location.reload() }, 5000)" x-on:beforeunload.window="clearInterval(refreshInterval)" @endif>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-4">
                                <x-badge :color="$audit->status === 'completed' ? 'green' : ($audit->isProcessing() ? 'blue' : ($audit->status === 'failed' ? 'red' : 'gray'))" size="lg">
                                    {{ ucfirst($audit->status) }}
                                </x-badge>

                                @if($audit->isProcessing())
                                    <span class="text-sm text-gray-500">Audit in progress... Page will auto-refresh every 5 seconds.</span>
                                @endif
                            </div>

                            @if($audit->isProcessing() && $audit->jobs_total > 0)
                                <div class="mt-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Progress</span>
                                        <span class="text-sm text-gray-600">{{ $audit->jobs_completed }} / {{ $audit->jobs_total }} jobs completed ({{ $audit->getProgressPercentage() }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $audit->getProgressPercentage() }}%"></div>
                                    </div>
                                    @if($audit->current_step)
                                        <p class="text-sm text-gray-500 mt-2">{{ $audit->current_step }}</p>
                                    @endif
                                </div>
                            @endif

                            @if($audit->hasFailedJobs())
                                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
                                    <p class="text-sm text-red-800">
                                        <strong>Warning:</strong> {{ $audit->jobs_failed }} {{ Str::plural('job', $audit->jobs_failed) }} failed during processing.
                                    </p>
                                    @if($audit->error_message)
                                        <p class="text-xs text-red-700 mt-1">{{ Str::limit($audit->error_message, 150) }}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Pages Crawled</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $audit->pages_crawled ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Issues</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $summary['total_issues'] }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Critical Issues</p>
                                    <p class="text-2xl font-semibold text-red-600">{{ $summary['critical_issues'] }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Broken Links</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $summary['broken_links'] }}</p>
                                </div>
                            </div>
                        </div>

                        @if($audit->score !== null)
                            <div class="flex-shrink-0 ml-8">
                                <div class="text-center">
                                    <div class="text-6xl font-bold {{ $audit->score >= 80 ? 'text-green-600' : ($audit->score >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $audit->score }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">Overall Score</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($audit->isProcessing())
                        <div class="mt-4">
                            <form action="{{ route('audits.cancel', $audit) }}" method="POST" class="inline">
                                @csrf
                                <x-danger-button type="submit">Cancel Audit</x-danger-button>
                            </form>
                        </div>
                    @endif

                    @if($audit->isCompleted() || $audit->isFailed())
                        <div class="mt-4 flex items-center gap-3">
                            <form action="{{ route('audits.restart', $audit) }}" method="POST" class="inline">
                                @csrf
                                <x-primary-button type="submit">Re-run Audit</x-primary-button>
                            </form>

                            <form action="{{ route('audits.destroy', $audit) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this audit?');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">Delete Audit</x-danger-button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            @if($audit->isCompleted())
                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('audits.results.issues', $audit) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Issues</h3>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['total_issues'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">View all issues →</p>
                        </div>
                    </a>

                    <a href="{{ route('audits.results.performance', $audit) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Performance</h3>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['total_pages'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">Pages analyzed →</p>
                        </div>
                    </a>

                    <a href="{{ route('audits.results.links', $audit) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Links</h3>
                            <p class="text-3xl font-bold {{ $summary['broken_links'] > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">{{ $summary['broken_links'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">Broken links →</p>
                        </div>
                    </a>

                    <a href="{{ route('audits.results.checkout', $audit) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">Checkout</h3>
                            <p class="text-sm text-gray-500 mt-2">View checkout flow analysis →</p>
                        </div>
                    </a>
                </div>

                <!-- Issues by Category -->
                @if(!empty($summary['issues_by_category']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Issues by Category</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            @foreach($summary['issues_by_category'] as $category => $count)
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-gray-900">{{ $count }}</p>
                                    <p class="text-sm text-gray-500 capitalize mt-1">{{ $category }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Issues by Severity -->
                @if(!empty($summary['issues_by_severity']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Issues by Severity</h3>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            @foreach(['critical', 'high', 'medium', 'low', 'info'] as $severity)
                                <div class="text-center">
                                    <p class="text-3xl font-bold {{ $severity === 'critical' ? 'text-red-600' : ($severity === 'high' ? 'text-orange-600' : 'text-gray-900') }}">
                                        {{ $summary['issues_by_severity'][$severity] ?? 0 }}
                                    </p>
                                    <p class="text-sm text-gray-500 capitalize mt-1">{{ $severity }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Export Options -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Report</h3>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('audits.report.pdf', $audit) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                PDF Report
                            </a>
                            <a href="{{ route('audits.report.csv', $audit) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                CSV Export
                            </a>
                            <a href="{{ route('audits.report.json', $audit) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                JSON Export
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
