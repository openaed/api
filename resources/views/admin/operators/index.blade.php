@extends('admin.layout')

@section('pagetitle', 'Operators')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">

        <table class="mb-5 w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Phone
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Website
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Defibrillators
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 border-t border-gray-100">
                @if($operators->isEmpty())
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            No operators yet.
                        </td>
                    </tr>
                @endif
                @foreach($operators as $operator)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $operator->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($operator->email)
                                <a href="mailto:{{ $operator->email }}"
                                    class="text-blue-500 hover:underline">{{ $operator->email }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($operator->phone)
                                <a href="tel:{{ str_replace(' ', '', $operator->phone) }}" class="text-blue-500 hover:underline">
                                    {{ $operator->phone }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($operator->website)
                                <a href="{{ $operator->website }}" class="text-blue-500 hover:underline" target="_blank"
                                    rel="noopener noreferrer">{{ $operator->website }} <x-heroicon-o-arrow-top-right-on-square
                                        width="16" height="16" class="inline" /></a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $operator->defibrillators->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.operators.details', $operator->id) }}"
                                class="text-blue-500 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5">{{ $operators->links() }}</div>
    </div>
@endsection