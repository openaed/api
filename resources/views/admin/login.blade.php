@extends('admin.guest-layout')

@section('content')
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-md">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Admin Login
                    </h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Sign in to access the OpenAED admin panel.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>

                        <input type="password" name="password" id="password" required autofocus
                            autocomplete="current-password" placeholder="Enter your password" class="
                                    block w-full rounded-lg border border-gray-300
                                    bg-white px-3 py-2.5
                                    text-gray-900 placeholder:text-gray-400
                                    shadow-sm
                                    focus:border-blue-500 focus:outline-none
                                    focus:ring-2 focus:ring-blue-500/20
                                ">

                        @error('password')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit" class="
                                w-full rounded-lg bg-blue-600
                                px-4 py-2.5
                                text-sm font-semibold text-white
                                shadow-sm
                                transition
                                hover:bg-blue-700
                                focus:outline-none focus:ring-2
                                focus:ring-blue-500 focus:ring-offset-2
                                cursor-pointer
                            ">
                        Login
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                OpenAED API Administration
            </p>

        </div>
    </div>
@endsection