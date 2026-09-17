@extends('admin.layout')

@section('pagetitle', 'Access tokens')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4">

        <table class="mb-5 w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Token
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Scope
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Expires at
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last used at
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Active
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 border-t border-gray-100">
                @if($tokens->isEmpty())
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            No access tokens yet.
                        </td>
                    </tr>
                @endif
                @foreach($tokens as $token)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $token->token }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $token->assigned_to }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $token->assignee_email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $token->scope[0] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($token->expires_at === null)
                                Never
                            @else
                                {{ \Carbon\Carbon::parse($token->expires_at)->format('Y-m-d H:i:s') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($token->last_used_at === null)
                                Never used
                            @else
                                {{ \Carbon\Carbon::parse($token->last_used_at)->format('Y-m-d H:i:s') }}<br />
                                <small>({{ \Carbon\Carbon::parse($token->last_used_at)->diffForHumans() }})</small>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($token->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                    <x-heroicon-o-check-circle width="16" height="16" />
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                    <x-heroicon-o-x-circle width="16" height="16" />
                                    Inactive
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5">{{ $tokens->links() }}</div>
    </div>
@endsection