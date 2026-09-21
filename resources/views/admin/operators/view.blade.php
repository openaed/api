@extends('admin.layout')

@section('pagetitle', 'Operator ' . $operator->name)

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">
        <div class="flex gap-5">
            <div class="grid">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Details</h2>
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">UUID</td>
                            <td>{{ $operator->id }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">Phone</td>
                            <td>{{ $operator->phone ?? "-" }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">Email</td>
                            <td>{{ $operator->email ?? "-" }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">Website</td>
                            <td>{{ $operator->website ?? "-" }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 font-medium text-gray-800">Date Added</td>
                            <td>{{ $operator->created_at }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="text-lg font-bold text-gray-900 my-4">Defibrillators ({{ $defibrillators->total() }})</h3>
                <table>
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                OSM ID
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
                    <tbody>
                        @foreach($defibrillators as $defibrillator)
                            <tr data-defibrillator="{{ $defibrillator->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="https://www.osm.org/node/{{ $defibrillator->osm_id }}" target="_blank"
                                        class="text-blue-500 hover:underline">{{ $defibrillator->osm_id }}
                                        <x-heroicon-o-arrow-top-right-on-square width="16" height="16" class="inline" /></a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $defibrillator->location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $defibrillator->created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.defibrillators.details', ['id' => $defibrillator->id]) }}"
                                        class="text-blue-500 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $defibrillators->links() }}
            </div>

            <div class="basis-lg">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
                <button id="delete-operator"
                    class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-800 cursor-pointer">
                    Delete
                </button>
            </div>
        </div>

    </div>

    {{-- Delete confirmation modal --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog"
        aria-modal="true" aria-labelledby="delete-modal-title">
        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>

                    <div>
                        <h3 id="delete-modal-title" class="text-lg font-semibold text-gray-900">
                            Delete operator?
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            Are you sure you want to delete operator
                            <span class="font-semibold text-gray-900">
                                {{ $operator->name }}
                            </span>?
                        </p>

                        <p class="mt-2 text-sm text-gray-500">
                            This action cannot be undone. Note that a future import may re-add this operator if it
                            still exists in OpenStreetMap. All defibrillators associated with this operator will have their
                            operator ID set to null.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 rounded-b-xl bg-gray-50 px-6 py-4">
                <button type="button" id="cancel-delete"
                    class="cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>

                <form method="POST" action="{{ route('admin.operators.delete', $operator->id) }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="cursor-pointer rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">
                        Delete operator
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButton = document.getElementById('delete-operator');
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