<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Audit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('audits.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="url" :value="__('Website URL')" />
                            <x-text-input
                                id="url"
                                name="url"
                                type="url"
                                class="mt-1 block w-full"
                                :value="old('url')"
                                required
                                autofocus
                                placeholder="https://example.com"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('url')" />
                            <p class="mt-1 text-sm text-gray-500">
                                Enter the full URL of the website you want to audit (including https://)
                            </p>
                        </div>

                        <div>
                            <x-input-label for="max_pages" :value="__('Maximum Pages to Crawl')" />
                            <x-text-input
                                id="max_pages"
                                name="max_pages"
                                type="number"
                                class="mt-1 block w-full"
                                :value="old('max_pages', 50)"
                                min="1"
                                max="500"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('max_pages')" />
                            <p class="mt-1 text-sm text-gray-500">
                                Limit the number of pages to crawl (1-500). Default is 50.
                            </p>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        The audit will analyze the following:
                                    </p>
                                    <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                                        <li>Page performance and Core Web Vitals</li>
                                        <li>SEO optimization</li>
                                        <li>Mobile responsiveness</li>
                                        <li>Broken links</li>
                                        <li>Checkout flow (if applicable)</li>
                                    </ul>
                                    <p class="mt-2 text-sm text-blue-700">
                                        Depending on the number of pages, the audit may take 10-30 minutes to complete.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>
                                {{ __('Start Audit') }}
                            </x-primary-button>

                            <a href="{{ route('audits.index') }}" class="text-sm text-gray-600 hover:underline">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
