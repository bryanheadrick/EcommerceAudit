<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('audits.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                New Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-stat-card
                    label="Total Audits"
                    :value="$stats['total_audits']"
                />

                <x-stat-card
                    label="Completed Audits"
                    :value="$stats['completed_audits']"
                />

                <x-stat-card
                    label="Processing"
                    :value="$stats['processing_audits']"
                />

                <x-stat-card
                    label="Average Score"
                    :value="$stats['average_score'] ? round($stats['average_score']) : 'N/A'"
                />

                <x-stat-card
                    label="Total Issues"
                    :value="$stats['total_issues']"
                />

                <x-stat-card
                    label="Critical Issues"
                    :value="$stats['critical_issues']"
                />
            </div>

            <!-- Recent Audits -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Audits</h3>

                    @if($recentAudits->isEmpty())
                        <p class="text-gray-500">No audits yet. <a href="{{ route('audits.create') }}" class="text-blue-600 hover:underline">Create your first audit</a>.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($recentAudits as $audit)
                                <div class="border-l-4 pl-4 {{ $audit->status === 'completed' ? 'border-green-500' : ($audit->isProcessing() ? 'border-blue-500' : 'border-red-500') }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <a href="{{ route('audits.show', $audit) }}" class="text-lg font-medium text-gray-900 hover:text-blue-600">
                                                {{ $audit->domain }}
                                            </a>
                                            <p class="text-sm text-gray-500">
                                                {{ $audit->url }}
                                            </p>
                                            <div class="mt-2 flex items-center gap-3">
                                                <x-badge :color="$audit->status === 'completed' ? 'green' : ($audit->isProcessing() ? 'blue' : 'red')">
                                                    {{ ucfirst($audit->status) }}
                                                </x-badge>
                                                <span class="text-sm text-gray-500">
                                                    {{ $audit->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                        @if($audit->score)
                                            <div class="flex-shrink-0 ml-4">
                                                <div class="text-3xl font-bold {{ $audit->score >= 80 ? 'text-green-600' : ($audit->score >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ $audit->score }}
                                                </div>
                                                <div class="text-xs text-gray-500 text-center">Score</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('audits.index') }}" class="text-sm text-blue-600 hover:underline">
                                View all audits →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if(!empty($issuesByCategory))
            <!-- Issues by Category -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Issues by Category</h3>
                    <div class="space-y-3">
                        @foreach($issuesByCategory as $category => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ $category }}</span>
                                <x-badge color="gray">{{ $count }}</x-badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if(!empty($topIssues))
            <!-- Top Issues -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Common Issues</h3>
                    <div class="space-y-3">
                        @foreach($topIssues as $issue)
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $issue->title }}</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <x-badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'high' ? 'orange' : 'yellow')" size="sm">
                                            {{ ucfirst($issue->severity) }}
                                        </x-badge>
                                        <x-badge color="gray" size="sm">{{ $issue->category }}</x-badge>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-gray-600">{{ $issue->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
