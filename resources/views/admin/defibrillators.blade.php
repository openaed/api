@extends('admin.layout')

@section('pagetitle', 'Defibrillators')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">

        <table>
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        OSM ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Exact location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Operator
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Location
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

            </tbody>
        </table>

        <span id="paginateLabel" class="px-4 py-2 text-gray-700"></span>

        <div class="flex justify-end">
            <button id="prev-button"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                < </button>
                    <span id="currentPageLabel" class="px-4 py-2 text-gray-700"></span>
                    <button id="next-button"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        >
                    </button>
        </div>

    </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentPage;
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('page')) {
            currentPage = parseInt(urlParams.get('page'));
        } else {
            currentPage = 1;
        }
        const tableBody = document.querySelector('tbody');
        const prevButton = document.querySelector('#prev-button');
        const nextButton = document.querySelector('#next-button');
        const pageLabel = document.querySelector('#paginateLabel');
        const currentPageLabel = document.querySelector('#currentPageLabel');
        const apiUrl = "{{ route('admin.defibrillators.paginated') }}";
        const loadDefibrillators = () => {
            fetch(`${apiUrl}?page=${currentPage}`)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    data.data.forEach(defibrillator => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                                                                                                                                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${defibrillator.osm_id}</td>
                                                                                                                                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${defibrillator.location || 'N/A'}</td>
                                                                                                                                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${defibrillator.raw_osm.operator || 'N/A'}</td>
                                                                                                                                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${photonReverse(defibrillator.latitude, defibrillator.longitude)}</td>
                                                                                                                                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(defibrillator.created_at).toLocaleDateString() || 'N/A'}</td>
                                                                                                                                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                                                                                                                                <a href="https://osm.org/node/${defibrillator.osm_id}" class="text-blue-600 hover:text-blue-900" target="_blank">OSM</a>
                                                                                                                                                                            </td>
                                                                                                                                                                        `;
                        tableBody.appendChild(row);
                    });

                    // Disable prev button if on first page
                    prevButton.disabled = currentPage === 1;
                    // Disable next button if on last page
                    nextButton.disabled = !data.next_page_url;

                    paginateLabel.textContent = `Showing ${data.from} to ${data.to} of ${data.total} defibrillators`;
                    currentPageLabel.textContent = `Page ${currentPage} of ${data.last_page}`;

                    const newUrl = `${window.location.pathname}?page=${currentPage}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
                });
        };

        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadDefibrillators();
            }
        });

        nextButton.addEventListener('click', () => {
            currentPage++;
            loadDefibrillators();
        });

        loadDefibrillators();

        const photonReverse = (lat, lon) => {
            const photonUrl = "{{ config('app.photon.url') }}";
            const cacheKey = `photon:${lat}:${lon}`;
            const cached = localStorage.getItem(cacheKey);

            if (cached) {
                return cached;
            } else {
                fetch(`${photonUrl}/reverse?lat=${lat}&lon=${lon}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data?.features?.length > 0) {
                            const feature = data.features[0].properties;
                            const address = `${firstValid([feature.street, feature.road, "Unknown road"])}, ${firstValid([feature.city, feature.village, feature.town, "Unknown town"])}, ${feature.countrycode}`;

                            localStorage.setItem(cacheKey, address);

                            return address;
                        }
                    });
            }
        };

    </script>
@endpush