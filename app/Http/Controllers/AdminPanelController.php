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


    // Defib methods
    function defibrillators()
    {
        $defibrillators = Defibrillator::orderBy('created_at', 'desc')->paginate(10, ['*'], 'p', request()->query('p', 1));
        return view('admin.defibrillators.index', ['defibrillators' => $defibrillators]);
    }

    function defibrillatorDetails($id)
    {
        $defibrillator = Defibrillator::find($id);

        if (!$defibrillator) {
            abort(404, 'Defibrillator not found');
        }

        $operator = $defibrillator->operator;
        $operatorDefibCount = $operator ? $operator->defibrillators()->count() : 0;

        return view('admin.defibrillators.view', ['defibrillator' => $defibrillator, 'operator' => $operator, 'operatorDefibCount' => $operatorDefibCount]);
    }

    /**
     * Find a defibrillator by ID
     * @param Request $request
     */
    function findDefibrillator(Request $request)
    {
        $typeId = $request->get('type_id');
        $id = $request->get('id');

        if (!$typeId || !$id) {
            return redirect()->back()->with('error_defibsearch', 'Missing required parameters: type_id and id.');
        }

        if (!in_array($typeId, ['osm', 'uuid'])) {
            return redirect()->back()->with('error_defibsearch', 'Invalid type_id. Must be "osm" or "uuid".');
        }

        if ($typeId === 'uuid' && !Str::isUuid($id)) {
            return redirect()->back()->with('error_defibsearch', 'Invalid UUID format.');
        }

        if ($typeId === 'osm' && !is_numeric($id)) {
            return redirect()->back()->with('error_defibsearch', 'Invalid OSM ID format. Must be a numeric value.');
        }

        if ($typeId === 'osm') {
            $defibrillator = Defibrillator::where('osm_id', $id)->first();
        } else {
            $defibrillator = Defibrillator::find($id);
        }

        if (!$defibrillator) {
            return redirect()->back()->with('error_defibsearch', 'Defibrillator not found.');
        }

        return redirect()->route('admin.defibrillators.details', ['id' => $defibrillator->id]);
    }

    /**
     * Delete a defibrillator by ID
     * @param Request $request
     * @param string $id The UUID of the defibrillator to delete
     */
    function deleteDefibrillator(Request $request, $id)
    {
        $defibrillator = Defibrillator::find($id);

        if (!$defibrillator) {
            return 404;
        }

        $defibrillator->delete();

        return redirect()->route('admin.defibrillators')->with('success', 'Defibrillator deleted successfully.');
    }

    // Operator methods
    function operators()
    {
        $operators = Operator::withCount('defibrillators')
            ->orderBy('defibrillators_count', 'desc')
            ->orderBy('id', 'asc')
            ->paginate(10, ['*'], 'p', request()->query('p', 1));

        return view('admin.operators.index', [
            'operators' => $operators
        ]);
    }

    function operatorDetails($id)
    {
        $operator = Operator::find($id);

        if (!$operator) {
            abort(404, 'Operator not found');
        }

        $defibrillators = $operator->defibrillators()->orderBy('created_at', 'desc')->paginate(10, ['*'], 'p', request()->query('p', 1));

        return view('admin.operators.view', ['operator' => $operator, 'defibrillators' => $defibrillators]);
    }

    function deleteOperator(Request $request, $id)
    {
        $operator = Operator::find($id);

        if (!$operator) {
            return 404;
        }

        Defibrillator::where('operator_id', $operator->id)->update(['operator_id' => null]);

        $operator->delete();

        return redirect()->route('admin.operators')->with('success', 'Operator deleted successfully.');
    }

    // Import methods
    function imports()
    {
        $imports = Import::orderBy('created_at', 'desc')->paginate(10, ['*'], 'p', request()->query('p', 1));
        return view('admin.imports', ['imports' => $imports]);
    }

    function triggerImport(Request $request)
    {
        $doFullImport = $request->boolean('full');

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

    // Access Token methods
    function accessTokens()
    {
        $tokens = AccessToken::orderBy('created_at', 'desc')->paginate(10, ['*'], 'p', request()->query('p', 1));
        return view('admin.access-tokens', ['tokens' => $tokens]);
    }
}