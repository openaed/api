<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenAED API Admin panel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="min-h-screen bg-gray-50">

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-20 hidden w-64 flex-col border-r border-gray-800 bg-gray-900 md:flex">

            {{-- Brand --}}
            <div class="flex h-16 items-center border-b border-gray-800 px-6">
                <div>
                    <div class="text-lg font-semibold text-white">
                        OpenAED
                    </div>
                    <div class="text-xs text-gray-400 uppercase">
                        {{ app()->environment() }} API
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 px-3 py-5">

                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white @if(Route::is('admin.dashboard')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-800 hover:text-white @endif">
                    <x-heroicon-o-home width="20" height="20" />

                    Dashboard
                </a>

                <a href="{{ route('admin.defibrillators') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white @if(Route::is('admin.defibrillators')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-800 hover:text-white @endif">
                    <x-heroicon-o-bolt width="20" height="20" />

                    Defibrillators
                </a>

                <a href="#"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">
                    <x-heroicon-o-key width="20" height="20" />

                    Access tokens
                </a>

                <div class="my-4 border-t border-gray-800"></div>

                <a href="#"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">
                    <x-heroicon-o-cog width="20" height="20" />

                    Settings
                </a>

            </nav>

            {{-- Sidebar footer --}}
            <div class="border-t border-gray-800 p-4">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf

                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400 transition hover:bg-gray-800 hover:text-white cursor-pointer">
                        <x-heroicon-o-arrow-right-end-on-rectangle width="20" height="20" />

                        Log out
                    </button>
                </form>
            </div>
        </aside>


        {{-- Main --}}
        <div class="md:pl-64">
            {{-- Content --}}
            <main class="p-6 lg:p-8">
                @hasSection('pagetitle')
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold tracking-tight text-gray-900">
                            @yield('pagetitle')
                        </h2>
                        @hasSection('pagedescription')
                            <p class="mt-1 text-sm text-gray-500">
                                @yield('pagedescription')
                            </p>
                        @endif
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
@stack('scripts')

</html>