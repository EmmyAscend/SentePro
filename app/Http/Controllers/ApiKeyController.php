<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage-api-keys');

        $tokens = $request->user()->tokens()->latest()->get();

        return view('api-keys.index', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-api-keys');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken($validated['name']);

        return redirect()->route('api-keys.index')
            ->with('status', 'API key created successfully.')
            ->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, int $token): RedirectResponse
    {
        $this->authorize('manage-api-keys');

        $request->user()->tokens()->whereKey($token)->delete();

        return redirect()->route('api-keys.index')->with('status', 'API key revoked.');
    }
}
