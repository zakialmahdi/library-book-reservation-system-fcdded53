<?php

namespace App\Http\Controllers;

use App\Actions\Fines\DeleteFineAction;
use App\Actions\Fines\IssueFineAction;
use App\Actions\Fines\SettleFineAction;
use App\Http\Requests\Fines\FineRequest;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FineController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Fine::class);

        return view('fines.index');
    }

    public function create()
    {
        Gate::authorize('create', Fine::class);

        return view('fines.index', ['openCreate' => true]);
    }

    public function store(FineRequest $request, IssueFineAction $action)
    {
        Gate::authorize('create', Fine::class);

        $action->execute($request->validated());

        return redirect()->route('fines.index')
            ->with('success', 'Fine issued successfully.');
    }

    public function show(Fine $fine)
    {
        Gate::authorize('view', $fine);

        $fine->load(['loan.user', 'loan.book']);

        return view('fines.show', ['fine' => $fine]);
    }

    public function edit(Fine $fine)
    {
        Gate::authorize('update', $fine);

        return view('fines.index', ['editFineId' => $fine->fine_id]);
    }

    public function update(Request $request, Fine $fine, SettleFineAction $action)
    {
        Gate::authorize('update', $fine);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:unpaid,paid,waived'],
        ]);

        $action->execute($fine, $validated['status']);

        return redirect()->route('fines.index')
            ->with('success', 'Fine status updated.');
    }

    public function destroy(Fine $fine, DeleteFineAction $action)
    {
        Gate::authorize('delete', $fine);

        $action->execute($fine);

        return redirect()->route('fines.index')
            ->with('success', 'Fine record deleted.');
    }
}
