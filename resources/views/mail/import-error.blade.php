@extends('mail.layout')

@section('content')
    <p>An error occurred during an import:</p>

    <span style="background-color: #ff7570; font-family: monospace; color: #8f0500;">
        &nbsp;{{ $errorMessage }}&nbsp;
    </span>

    <p>Here are the details:</p>

    <p>
        <b>Import ID:</b> {{ $import->id }}<br />
        <b>Time:</b> {{ $import->updated_at }}<br />
        <b>Full import:</b> {{ $import->is_full_import ? 'yes' : 'no' }}<br />
        <b>Instance:</b> {{ config('app.url') }}
    </p>

    Thanks,<br />
    {{ config('app.name') }}
@endsection
