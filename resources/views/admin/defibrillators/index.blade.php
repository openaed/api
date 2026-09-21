@extends('admin.layout')

@section('pagetitle', 'Defibrillators')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm px-4 py-2 mb-5">
        <label for="search">Search by ID</label>
        <form action="{{ route('admin.defibrillators.find') }}" method="GET" class="flex mt-1">
            <select name="type_id" id="search_type_id"
                class="p-2 rounded-s-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="osm">OSM ID</option>
                <option value="uuid">UUID</option>
            </select>
            <input type="search" name="id" id="search_id"
                class="p-2 rounded-e-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="ID" required>
            <button type="submit" id="search_button"
                class="ml-2 px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-black cursor-pointer">
                Go
            </button>
        </form>
        @session('error_defibsearch')
            <span class="ml-4 text-red-500">{{ session('error_defibsearch') }}</span>
        @endsession
    </div>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">

        <table class="mb-5 w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        OSM ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Operator
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date added
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Details
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 border-t border-gray-100">
                @if($defibrillators->isEmpty())
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            No defibrillators yet.
                        </td>
                    </tr>
                @endif
                @foreach ($defibrillators as $defibrillator)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="https://www.osm.org/node/{{ $defibrillator->osm_id }}" target="_blank"
                                class="text-blue-500 hover:text-blue-700">{{ $defibrillator->osm_id }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-wrap" id="aed-{{ $defibrillator->id }}"
                            data-latitude="{{ $defibrillator->latitude }}" data-longitude="{{ $defibrillator->longitude }}">
                            @if($defibrillator->location)
                                {{ $defibrillator->location }}
                            @else
                                <i class="text-gray-700">Exact location missing</i>
                            @endif
                            <span id="aed-{{ $defibrillator->id }}-address" class="text-sm text-gray-500 block"></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $defibrillator->operator ? $defibrillator->operator['name'] : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $defibrillator->created_at->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.defibrillators.details', ['id' => $defibrillator->id]) }}"
                                class="text-blue-500 hover:text-blue-700">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5">{{ $defibrillators->links() }}</div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photonUrl = "{{ config('app.photon.url') }}";

            const newAeds = document.querySelectorAll('[id^="aed-"]');

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