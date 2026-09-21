@extends('admin.layout')

@section('pagetitle', 'Defibrillator ' . $defibrillator->id)

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
        <div class="flex gap-5">
            <div class="grid">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Details</h2>
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">OSM ID</td>
                            <td><a href="https://www.osm.org/node/{{ $defibrillator->osm_id }}" target="_blank"
                                    class="text-blue-500 hover:underline">{{ $defibrillator->osm_id }} <x-heroicon-o-arrow-top-right-on-square width="16" height="16" class="inline" /></a></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">UUID</td>
                            <td>{{ $defibrillator->id }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">Exact location</td>
                            <td>{{ $defibrillator->location }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">Date Added</td>
                            <td>{{ $defibrillator->created_at }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="text-lg font-bold text-gray-900 mt-4">Operator</h3>
                @if($operator)
                    <a href="{{ route('admin.operators.details', ['id' => $operator->id]) }}"
                        class="text-lg underline md:no-underline hover:underline font-medium">{{ $operator->name }}</a>
                    @if($operator->phone)
                        <a href="tel:{{ $operator->phone }}" target="_blank"
                            class="text-blue-500 hover:text-blue-700">
                            {{ $operator->phone }}
                        </a>
                    @endif
                    @if($operator->email)
                        <a href="mailto:{{ $operator->email }}" target="_blank"
                            class="text-blue-500 hover:text-blue-700">
                            {{ $operator->email }}
                        </a>
                    @endif
                    @if($operator->website)
                        <a href="{{ $operator->website }}" target="_blank" class="text-blue-500 hover:text-blue-700">
                            {{ $operator->website }} <x-heroicon-o-arrow-top-right-on-square width="16" height="16" class="inline" />
                        </a>
                    @endif
                    <p class="text-gray-500 mt-2">This operator has {{ $operatorDefibCount }} defibrillator(s) in the system.</p>
                @else
                    <p class="text-gray-500">No operator information available.</p>
                @endif
            </div>

            <div class="basis-lg">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
                <button id="delete-defibrillator" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-800 cursor-pointer">
                    Delete
                </button>
            </div>

            <div class="basis-lg">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Location</h2>
                <div id="map" class="w-full h-96 rounded-lg"></div>
                <span class="text-sm text-gray-600">{{ $defibrillator->latitude }}, {{ $defibrillator->longitude }}</span>
            </div>

        </div>

    </div>

{{-- Delete confirmation modal --}}
<div
    id="delete-modal"
    class="fixed inset-0 z-2000 hidden items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="delete-modal-title"
>
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg
                        class="h-6 w-6 text-red-700"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                        />
                    </svg>
                </div>

                <div>
                    <h3 id="delete-modal-title" class="text-lg font-semibold text-gray-900">
                        Delete defibrillator?
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Are you sure you want to delete defibrillator
                        <span class="font-semibold text-gray-900">
                            {{ $defibrillator->osm_id }}
                        </span>?
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        This action cannot be undone. Note that a future import may re-add this defibrillator if it still exists in OpenStreetMap.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 rounded-b-xl bg-gray-50 px-6 py-4">
            <button
                type="button"
                id="cancel-delete"
                class="cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Cancel
            </button>

            <form
                method="POST"
                action="{{ route('admin.defibrillators.delete', $defibrillator->id) }}"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="cursor-pointer rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800"
                >
                    Delete defibrillator
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('map').setView([{{ $defibrillator->latitude }}, {{ $defibrillator->longitude }}], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const marker = L.marker([{{ $defibrillator->latitude }}, {{ $defibrillator->longitude }}]).addTo(map);
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButton = document.getElementById('delete-defibrillator');
            const deleteModal = document.getElementById('delete-modal');
            const cancelButton = document.getElementById('cancel-delete');

            function openDeleteModal() {
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');

                cancelButton.focus();
            }

            function closeDeleteModal() {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');

                deleteButton.focus();
            }

            deleteButton.addEventListener('click', openDeleteModal);
            cancelButton.addEventListener('click', closeDeleteModal);

            // Close when clicking the dark backdrop.
            deleteModal.addEventListener('click', function (event) {
                if (event.target === deleteModal) {
                    closeDeleteModal();
                }
            });

            // Close with Escape.
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                    closeDeleteModal();
                }
            });
        });
    </script>
@endpush