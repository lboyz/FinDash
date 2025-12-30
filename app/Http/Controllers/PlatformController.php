<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Platforms are currently global or shared? The migration schema had just 'name' and unique.
        // If they are global, regular users shouldn't edit them unless admin.
        // Assuming for now we list them, but maybe we want UserWallet?
        // The Prompt said "fitur yg diperlukan".
        // Let's assume for now we just verify existence. 
        // If Platforms are System-wide (created by migration), managing them might be Admin only.
        // But users need to see them.
        
        $platforms = Platform::orderBy('name')->get();

        return Inertia::render('Platforms/Index', [
            'platforms' => $platforms,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Allow creating new platforms? 
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:platforms,name'],
        ]);

        Platform::create($validated);

        return redirect()->back()->with('success', 'Platform created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Platform $platform)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:platforms,name,' . $platform->id],
        ]);

        $platform->update($validated);

        return redirect()->back()->with('success', 'Platform updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Platform $platform) {
        // Check if has transactions...
        if ($platform->transactions()->exists()) {
             return redirect()->back()->with('error', 'Cannot delete platform with transactions.');
        }
        $platform->delete();
        return redirect()->back()->with('success', 'Platform deleted successfully.');
    }
}
