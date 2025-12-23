<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Audit Comparison - {{ $currentAudit->domain }}
                </h2>
            </div>
            <a href="{{ route('audits.show', $currentAudit) }}" class="text-sm text-gray-600 hover:underline">
                Back to Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Audit Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Current Audit</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ $currentAudit->domain }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $currentAudit->completed_at?->format('M d, Y H:i') }}</p>
                        @if($currentAudit->score)
                            <p class="text-4xl font-bold {{ $currentAudit->score >= 80 ? 'text-green-600' : ($currentAudit->score >= 60 ? 'text-yellow-600' : 'text-red-600') }} mt-4">
                                {{ $currentAudit->score }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Previous Audit</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ $previousAudit->domain }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $previousAudit->completed_at?->format('M d, Y H:i') }}</p>
                        @if($previousAudit->score)
                            <p class="text-4xl font-bold {{ $previousAudit->score >= 80 ? 'text-green-600' : ($previousAudit->score >= 60 ? 'text-yellow-600' : 'text-red-600') }} mt-4">
                                {{ $previousAudit->score }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Score Comparison -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Overall Score Change</h3>
                    <div class="flex items-center gap-6">
                        <div class="text-5xl font-bold {{ $comparison['score_change'] > 0 ? 'text-green-600' : ($comparison['score_change'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $comparison['score_change'] > 0 ? '+' : '' }}{{ $comparison['score_change'] }}
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Score {{ $comparison['score_change'] > 0 ? 'Improvement' : ($comparison['score_change'] < 0 ? 'Decline' : 'No Change') }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $previousAudit->score }} → {{ $currentAudit->score }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metrics Comparison -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Issues Change</h4>
                        <p class="text-3xl font-bold {{ $comparison['issues_change'] < 0 ? 'text-green-600' : ($comparison['issues_change'] > 0 ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $comparison['issues_change'] > 0 ? '+' : '' }}{{ $comparison['issues_change'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $comparison['issues_change'] < 0 ? 'Fewer' : ($comparison['issues_change'] > 0 ? 'More' : 'Same') }} issues than before
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Critical Issues</h4>
                        <p class="text-3xl font-bold {{ $comparison['critical_issues_change'] < 0 ? 'text-green-600' : ($comparison['critical_issues_change'] > 0 ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $comparison['critical_issues_change'] > 0 ? '+' : '' }}{{ $comparison['critical_issues_change'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $comparison['critical_issues_change'] < 0 ? 'Fewer' : ($comparison['critical_issues_change'] > 0 ? 'More' : 'Same') }} critical issues
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Broken Links</h4>
                        <p class="text-3xl font-bold {{ $comparison['broken_links_change'] < 0 ? 'text-green-600' : ($comparison['broken_links_change'] > 0 ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $comparison['broken_links_change'] > 0 ? '+' : '' }}{{ $comparison['broken_links_change'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $comparison['broken_links_change'] < 0 ? 'Fewer' : ($comparison['broken_links_change'] > 0 ? 'More' : 'Same') }} broken links
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Performance</h4>
                        <p class="text-3xl font-bold {{ $comparison['performance_change']['change'] > 0 ? 'text-green-600' : ($comparison['performance_change']['change'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $comparison['performance_change']['change'] > 0 ? '+' : '' }}{{ number_format($comparison['performance_change']['change'], 1) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            Avg performance score change
                        </p>
                    </div>
                </div>
            </div>

            <!-- New & Resolved Issues -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">New Issues</h3>
                        <p class="text-4xl font-bold {{ $comparison['new_issues'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $comparison['new_issues'] }}
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Issues that appeared in the current audit
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Resolved Issues</h3>
                        <p class="text-4xl font-bold {{ $comparison['resolved_issues'] > 0 ? 'text-green-600' : 'text-gray-600' }}">
                            {{ $comparison['resolved_issues'] }}
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Issues that were fixed since previous audit
                        </p>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary</h3>

                    @php
                        $improvements = 0;
                        $regressions = 0;

                        if ($comparison['score_change'] > 0) $improvements++;
                        if ($comparison['score_change'] < 0) $regressions++;

                        if ($comparison['issues_change'] < 0) $improvements++;
                        if ($comparison['issues_change'] > 0) $regressions++;

                        if ($comparison['critical_issues_change'] < 0) $improvements++;
                        if ($comparison['critical_issues_change'] > 0) $regressions++;

                        if ($comparison['broken_links_change'] < 0) $improvements++;
                        if ($comparison['broken_links_change'] > 0) $regressions++;
                    @endphp

                    <div class="prose max-w-none">
                        @if($improvements > $regressions)
                            <x-alert type="success">
                                <p class="font-semibold">Great progress!</p>
                                <p class="mt-1">Your site has shown improvement in {{ $improvements }} {{ Str::plural('metric', $improvements) }} since the previous audit.</p>
                            </x-alert>
                        @elseif($regressions > $improvements)
                            <x-alert type="warning">
                                <p class="font-semibold">Attention needed</p>
                                <p class="mt-1">Your site has regressed in {{ $regressions }} {{ Str::plural('metric', $regressions) }} since the previous audit. Review the issues to identify areas for improvement.</p>
                            </x-alert>
                        @else
                            <x-alert type="info">
                                <p class="font-semibold">Stable performance</p>
                                <p class="mt-1">Your site's overall performance has remained relatively stable since the previous audit.</p>
                            </x-alert>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
