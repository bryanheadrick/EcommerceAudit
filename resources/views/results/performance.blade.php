<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Performance - {{ $audit->domain }}
                </h2>
            </div>
            <a href="{{ route('audits.show', $audit) }}" class="text-sm text-gray-600 hover:underline">
                Back to Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Device Toggle -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('audits.results.performance', ['audit' => $audit, 'device' => 'mobile']) }}"
                           class="px-4 py-2 rounded-md {{ $deviceType === 'mobile' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Mobile
                        </a>
                        <a href="{{ route('audits.results.performance', ['audit' => $audit, 'device' => 'desktop']) }}"
                           class="px-4 py-2 rounded-md {{ $deviceType === 'desktop' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Desktop
                        </a>
                    </div>
                </div>
            </div>

            @if($averageMetrics)
                <!-- Core Web Vitals -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Core Web Vitals (Average)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <p class="text-sm text-gray-500">LCP (Largest Contentful Paint)</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['lcp'] < 2.5 ? 'text-green-600' : ($averageMetrics['lcp'] < 4.0 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['lcp'], 2) }}s
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Good: &lt; 2.5s</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">FID (First Input Delay)</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['fid'] < 100 ? 'text-green-600' : ($averageMetrics['fid'] < 300 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['fid'], 0) }}ms
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Good: &lt; 100ms</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">CLS (Cumulative Layout Shift)</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['cls'] < 0.1 ? 'text-green-600' : ($averageMetrics['cls'] < 0.25 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['cls'], 3) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Good: &lt; 0.1</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lighthouse Scores -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lighthouse Scores (Average)</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-sm text-gray-500">Performance</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['performance_score'] >= 90 ? 'text-green-600' : ($averageMetrics['performance_score'] >= 50 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['performance_score'], 0) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Accessibility</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['accessibility_score'] >= 90 ? 'text-green-600' : ($averageMetrics['accessibility_score'] >= 50 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['accessibility_score'], 0) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">SEO</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['seo_score'] >= 90 ? 'text-green-600' : ($averageMetrics['seo_score'] >= 50 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['seo_score'], 0) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Best Practices</p>
                                <p class="text-3xl font-bold {{ $averageMetrics['best_practices_score'] >= 90 ? 'text-green-600' : ($averageMetrics['best_practices_score'] >= 50 ? 'text-yellow-600' : 'text-red-600') }} mt-2">
                                    {{ number_format($averageMetrics['best_practices_score'], 0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Per-Page Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance by Page</h3>

                    @if($pages->isEmpty())
                        <p class="text-gray-500 text-center py-8">No performance data available.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LCP</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLS</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pages as $page)
                                        @php
                                            $metric = $page->performanceMetrics->first();
                                        @endphp
                                        @if($metric)
                                            <tr>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <a href="{{ $page->url }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ Str::limit($page->url, 50) }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 text-sm">
                                                    <span class="font-semibold {{ $metric->lighthouse_performance_score >= 90 ? 'text-green-600' : ($metric->lighthouse_performance_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                                        {{ $metric->lighthouse_performance_score }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm {{ $metric->lcp < 2.5 ? 'text-green-600' : ($metric->lcp < 4.0 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ number_format($metric->lcp, 2) }}s
                                                </td>
                                                <td class="px-6 py-4 text-sm {{ $metric->fid < 100 ? 'text-green-600' : ($metric->fid < 300 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ number_format($metric->fid, 0) }}ms
                                                </td>
                                                <td class="px-6 py-4 text-sm {{ $metric->cls < 0.1 ? 'text-green-600' : ($metric->cls < 0.25 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ number_format($metric->cls, 3) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
