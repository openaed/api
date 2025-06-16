<?php

namespace App\Http\Controllers;

use \App\Models\Operator;
use App\Models\Defibrillator;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function getOperators(Request $request)
    {
        $operators = Operator::all();

        $operators->makeHidden(['created_at', 'updated_at']);
        $operators->each(function ($operator) {
            $operator->defibrillators = Defibrillator::where('operator_id', $operator->id)->count();
        });

        return response()->json($operators);
    }

    public function getOperatorById($id)
    {
        $operator = Operator::find($id);

        if (!$operator) {
            return response()->json(['error' => 'Operator not found'], 404);
        }

        $operator->makeHidden(['created_at', 'updated_at']);
        $operator->defibrillators = Defibrillator::where('operator_id', $operator->id)->count();

        return response()->json($operator);
    }

    public function getDefibrillatorsByOperatorId($id)
    {
        $operator = Operator::find($id);

        if (!$operator) {
            return response()->json(['error' => 'Operator not found'], 404);
        }

        $defibrillators = $operator->defibrillators;

        $operator->makeHidden(['created_at', 'updated_at', 'defibrillators']);

        return response()->json([
            'operator' => $operator,
            'defibrillators' => $defibrillators
        ]);
    }
}