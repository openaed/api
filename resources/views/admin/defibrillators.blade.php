@extends('admin.layout')

@section('pagetitle', 'Defibrillators')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">

        <table class="mb-5">
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
                        Date added
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Details
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 border-t border-gray-100">
                @foreach ($defibrillators as $defibrillator)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="https://www.osm.org/node/{{ $defibrillator->osm_id }}" target="_blank"
                                class="text-blue-500 hover:text-blue-700">{{ $defibrillator->osm_id }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-wrap">
                            {{ $defibrillator->location }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $defibrillator->operator ? $defibrillator->operator['name'] : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $defibrillator->created_at->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5">{{ $defibrillators->links() }}</div>
    </div>
@endsection