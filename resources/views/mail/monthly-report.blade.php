@extends('mail.layout')

@section('content')
    Here's the monthly report for instance <b>{{ config('app.url') }}</b> for the month of
    {{ $firstDayOfMonth->format('F Y') }}.<br />
    <small>({{ $firstDayOfMonth->format('d.m.Y H:i') }} - {{ $lastDayOfMonth->format('d.m.y H:i') }})</small><br />

    <br />
    <h3>Summary</h3>
    <p>
        In the month of {{ $firstDayOfMonth->format('F Y') }}, we had a total of <b>{{ $totalRequests }}</b> new
        API calls.<br />
        {{ $authenticatedRequests }} of these requests were authenticated.
    </p>

    <p>
        Right now, {{ $totalDefibrillators }} are registered in the system.<br />
        {{ $newDefibrillators }} of these defibrillators were added this month.
    </p>

    @if ($newAccessTokens > 0)
        <p>
            Lastly, {{ $newAccessTokens }} new access token(s) were created this month.
            That brings us to a total of {{ $totalAccessTokens }} access tokens in the system.
            The newest users are:
        <ul>
            @foreach ($newestAccessTokens as $token)
                <li>{{ $token->assigned_to }} ({{ $token->assignee_email }})</li>
            @endforeach
        </ul>
        </p>
    @else
        <p>
            No new access tokens were created this month.
            That keeps us at a total of {{ $totalAccessTokens }} access tokens in the system.
        </p>
    @endif

    <br />
    Thanks!
@endsection
