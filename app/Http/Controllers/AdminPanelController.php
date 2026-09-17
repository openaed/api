<?php

namespace App\Http\Controllers;

use App\Jobs\ImportDefibrillators;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Support\Str;
use App\Models\Defibrillator;
use App\Models\Operator;
use App\Models\AccessToken;
use App\Models\EventLog;
use App\Models\Import;

class AdminPanelController extends Controller
{
    /**
     * Show the admin login form.
     */
    function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Handle the admin login.
     */
    function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $adminPassword = config('app.admin.password');
        if (!$adminPassword) {
            return response()->json(['message' => 'Admin password not set. Admin panel unavailable.'], 500);
        }

        if ($request->password !== $adminPassword) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        EventLog::create([
            'id' => Str::uuid(),
            'type' => 'admin_login',
            'description' => 'Successful admin login',
            'data' => json_encode(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
        ]);

        // Password is correct, set a cookie
        $cookie = cookie('admin_pass', Hash::make($adminPassword), 60); // Cookie valid for 60 minutes

        return response()->redirectToRoute('admin.dashboard')->withCookie($cookie);
    }

    function logout(Request $request)
    {
        // Clear the admin_pass cookie
        $cookie = cookie('admin_pass', '', -1); // Set cookie to expire immediately

        return redirect()->route('admin.login')->withCookie($cookie);
    }

    /**
     * Show the admin dashboard.
     */
    function dashboard()
    {
        $countDefibrillators = Defibrillator::count();
        $countOperators = Operator::count();
        $countAccessTokens = AccessToken::count();

        $fiveNewestDefibrillators = Defibrillator::orderBy('created_at', 'desc')->take(5)->get();
        $lastImport = Import::orderBy('created_at', 'desc')->first();

        return view('admin.dashboard', [
            'countDefibrillators' => $countDefibrillators,
            'countOperators' => $countOperators,
            'countAccessTokens' => $countAccessTokens,
            'fiveNewDefibrillators' => $fiveNewestDefibrillators,
            'lastImport' => $lastImport,
        ]);
    }

    /**
     * Get a paginated list of defibrillators for the admin panel.
     * @param Request $request
     * @return JsonResponse
     */
    function defibrillatorsPaginated(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $currentPage = $request->query('page', 1);
        $defibrillators = Defibrillator::orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $currentPage);

        return response()->json($defibrillators);
    }

    function defibrillators()
    {
        return view('admin.defibrillators');
    }

    function imports()
    {
        $imports = Import::orderBy('created_at', 'desc')->paginate(10, ['*'], 'p', request()->query('page', 1));
        return view('admin.imports', ['imports' => $imports]);
    }

    function triggerImport(Request $request)
    {
        $doFullImport = $request->get('full', false) === 'true';

        $uuid = Str::uuid()->toString();
        try {
            ImportDefibrillators::dispatch($doFullImport, null, $uuid);

            return response()->json([
                'import_id' => $uuid
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to trigger import: ' . $e->getMessage()
            ], 500);
        }
    }
}