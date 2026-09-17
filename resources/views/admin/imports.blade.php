@extends('admin.layout')

@section('pagetitle', 'Imports')

@section('content')

    <div class="flex">
        <button id="btnTriggerFullImport"
            class="mb-4 rounded-lg bg-green-700 px-2 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 cursor-pointer">
            <x-heroicon-o-arrow-down-on-square-stack class="inline" width="20" height="20" />
            Trigger full import
        </button>

        <button id="btnTriggerUpdate"
            class="mb-4 ml-2 rounded-lg bg-green-600 px-2 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 cursor-pointer">
            <x-heroicon-o-arrow-path class="inline" width="20" height="20" />
            Trigger update
        </button>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">

        <table>
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Import ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Started at
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Finished at
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        AEDs imported
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Import type
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 border-t border-gray-100">
                @foreach($imports as $import)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" title="{{ $import->id }}">
                            {{ substr($import->id, 0, 8) . "..." }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @switch($import->status)
                                @case('requesting')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                        <x-heroicon-o-clock width="16" height="16" />
                                        Requesting
                                    </span>
                                    @break

                                @case('processing') @case('updating')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                        <x-heroicon-o-arrow-path width="16" height="16" />
                                        In progress
                                    </span>
                                    @break

                                @case('finished')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                        <x-heroicon-o-check-circle width="16" height="16" />
                                        Completed
                                    </span>
                                    @break

                                @case('errored')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                        <x-heroicon-o-x-circle width="16" height="16" />
                                        Failed
                                    </span>
                                    @break

                                @default
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                        Unknown
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($import->started_at)->format('Y-m-d H:i') }} ({{ \Carbon\Carbon::parse($import->started_at)->diffForHumans() }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($import->finished_at)
                                {{ \Carbon\Carbon::parse($import->finished_at)->format('Y-m-d H:i') }} ({{ \Carbon\Carbon::parse($import->finished_at)->diff(\Carbon\Carbon::parse($import->started_at)) }})
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $import->defibrillators }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($import->is_full_import)
                                <x-heroicon-o-arrow-down-on-square-stack class="inline" width="16" height="16" />
                                Full import
                            @else
                                <x-heroicon-o-arrow-path class="inline" width="16" height="16" />
                                Update
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $imports->links() }} 
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnTriggerFullImport = document.getElementById('btnTriggerFullImport');
        const btnTriggerUpdate = document.getElementById('btnTriggerUpdate');

        btnTriggerFullImport.addEventListener('click', function() {
            if (confirm('Are you sure you want to trigger a full import? This may take a while.')) {
                fetch('{{ route('admin.imports.trigger') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ full: true })
                })
                .then(response => response.json())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while triggering the import.');
                });
            }
        });

        btnTriggerUpdate.addEventListener('click', function() {
            if (confirm('Are you sure you want to trigger an update?')) {
                fetch('{{ route('admin.imports.trigger') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ full: false })
                })
                .then(response => response.json())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while triggering the update.');
                });
            }
        });
    });
</script>