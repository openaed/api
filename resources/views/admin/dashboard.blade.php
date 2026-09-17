@extends('admin.layout')

@section('pagetitle', 'Overview')
@section('pagedescription', 'Statistics about the API')

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Defibrillators
                    </p>

                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ $countDefibrillators }}
                    </p>
                </div>

                <div class="rounded-lg bg-blue-50 p-2.5 text-blue-600">
                    <x-heroicon-o-bolt width="20" height="20" />
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-400">
                AEDs in database
            </p>
        </div>


        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Operators
                    </p>

                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ $countOperators }}
                    </p>
                </div>

                <div class="rounded-lg bg-green-50 p-2.5 text-green-600">
                    <x-heroicon-o-wrench width="20" height="20" />
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-400">
                AED operators in database
            </p>
        </div>


        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Active access tokens
                    </p>

                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ $countAccessTokens }}
                    </p>
                </div>

                <div class="rounded-lg bg-purple-50 p-2.5 text-purple-600">
                    <x-heroicon-o-key width="20" height="20" />
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-400">
                Currently enabled keys
            </p>
        </div>


        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Last import
                    </p>

                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ \Carbon\Carbon::parse($lastImport->created_at ?? null)->format('Y-m-d H:i') ?? 'Never' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($lastImport->created_at ?? null)->diffForHumans() ?? '' }}
                    </p>
                </div>

                <div class="rounded-lg bg-yellow-50 p-2.5 text-yellow-600">
                    <x-heroicon-s-arrow-down-tray width="20" height="20" />
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-400">
                The date and time of the last import.
            </p>

        </div>
    </div>


    {{-- Lower content --}}
    <div class="mt-8 grid gap-6">

        {{-- Recent activity --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="font-semibold text-gray-900">
                        Newest defibrillators
                    </h3>

                    <p class="mt-0.5 text-sm text-gray-500">
                        Latest changes to the OpenAED database.
                    </p>
                </div>
            </div>

            <div class="divide-y divide-gray-100">

                @foreach($fiveNewDefibrillators as $defibrillator)
                    <div class="flex items-center gap-4 px-6 py-4" id="newaed-{{ $defibrillator->id }}"
                        data-latitude="{{ $defibrillator->latitude }}" data-longitude="{{ $defibrillator->longitude }}">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600">
                            <x-heroicon-o-plus width="20" height="20" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                Defibrillator <code
                                    class="bg-gray-100 text-gray-800 px-2 py-1 rounded"> {{ $defibrillator->id }}</code>
                                (OSM ID:
                                {{ $defibrillator->osm_id ?? 'N/A' }})
                            </p>
                            <p class="truncate text-sm text-gray-500" id="newaed-{{ $defibrillator->id }}-address">
                                Loading location...
                            </p>
                        </div>

                        <span class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($defibrillator->created_at)->diffForHumans() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photonUrl = "{{ config('app.photon.url') }}";

            const newAeds = document.querySelectorAll('[id^="newaed-"]');

            for (const defibElement of newAeds) {
                if (defibElement.id.endsWith('-address')) continue;

                const latitude = defibElement.getAttribute('data-latitude');
                const longitude = defibElement.getAttribute('data-longitude');

                const cacheKey = `photon:${latitude}:${longitude}`;
                const cached = localStorage.getItem(cacheKey);

                if (cached) {
                    defibElement.querySelector('[id$="-address"]').textContent = cached;
                } else {
                    fetch(`${photonUrl}/reverse?lat=${latitude}&lon=${longitude}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data?.features?.length > 0) {
                                const feature = data.features[0].properties;
                                const address = `${firstValid([feature.street, feature.road, "Unknown road"])}, ${firstValid([feature.city, feature.village, feature.town, "Unknown town"])}, ${feature.countrycode}`;

                                localStorage.setItem(cacheKey, address);

                                defibElement.querySelector('[id$="-address"]').textContent = address;
                            }
                        });
                }
            }
        });
    </script>
@endpush