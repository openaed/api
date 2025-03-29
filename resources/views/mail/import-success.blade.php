@extends('mail.layout')

@section('content')
    <p>An import was finished successfully!</p>

    <p>Here are the details:</p>

    <p>
        <b>Import ID:</b> {{ $import->id }}<br />
        <b>Time:</b> {{ $import->finished_at }}<br />
        <b>Full import:</b> {{ $import->is_full_import ? 'yes' : 'no' }}<br />
        <b>Defibrillators:</b> {{ $import->defibrillators }}<br />
        <b>Instance:</b> {{ config('app.url') }}
    </p>

    Thanks,<br />
    {{ config('app.name') }}
@endsection
