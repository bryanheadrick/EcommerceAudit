<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Checkout Flow - {{ $audit->domain }}
                </h2>
            </div>
            <a href="{{ route('audits.show', $audit) }}" class="text-sm text-gray-600 hover:underline">
                Back to Audit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Checkout Steps -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Checkout Steps</h3>

                    @if($checkoutSteps->isEmpty())
                        <p class="text-gray-500 text-center py-8">No checkout flow data available.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($checkoutSteps as $step)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <x-badge color="gray">
                                                    Step {{ $step->step_number }}
                                                </x-badge>
                                                <x-badge :color="$step->completed ? 'green' : 'red'">
                                                    {{ $step->completed ? 'Completed' : 'Failed' }}
                                                </x-badge>
                                                @if($step->duration)
                                                    <span class="text-sm text-gray-500">
                                                        {{ number_format($step->duration / 1000, 2) }}s
                                                    </span>
                                                @endif
                                            </div>

                                            <h4 class="text-lg font-semibold text-gray-900">{{ $step->step_name }}</h4>

                                            @if($step->step_url)
                                                <p class="text-sm text-gray-500 mt-2">
                                                    <a href="{{ $step->step_url }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ $step->step_url }}
                                                    </a>
                                                </p>
                                            @endif

                                            @if($step->error_message)
                                                <div class="mt-3 bg-red-50 border-l-4 border-red-400 p-3">
                                                    <p class="text-sm text-red-800">{{ $step->error_message }}</p>
                                                </div>
                                            @endif

                                            @if($step->screenshot_path)
                                                <div class="mt-3">
                                                    <a href="{{ asset('storage/' . $step->screenshot_path) }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                                        View Screenshot →
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Checkout Issues -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Checkout-Related Issues</h3>

                    @if($checkoutIssues->isEmpty())
                        <p class="text-gray-500 text-center py-8">No checkout-related issues found.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($checkoutIssues as $issue)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <x-badge :color="$issue->severity === 'critical' ? 'red' : ($issue->severity === 'high' ? 'orange' : 'yellow')">
                                                    {{ ucfirst($issue->severity) }}
                                                </x-badge>
                                            </div>

                                            <h4 class="text-lg font-semibold text-gray-900">{{ $issue->title }}</h4>
                                            <p class="text-sm text-gray-700 mt-2">{{ $issue->description }}</p>

                                            <div class="mt-3 bg-blue-50 border-l-4 border-blue-400 p-3">
                                                <p class="text-sm font-medium text-blue-900">Recommendation:</p>
                                                <p class="text-sm text-blue-800 mt-1">{{ $issue->recommendation }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
