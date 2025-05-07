<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AccessController extends Controller
{

    public function index()
    {
        $admins = Admin::all();
        return view('admin.access-control', compact('admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
        ]);

        Admin::create($validated);

        return redirect()->back()->with('success', 'Admin created successfully.');
    }

    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:admins,email,{$admin->id}",
        ]);

        $admin->update($validated);

        return redirect()->back()->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();
        return redirect()->back()->with('success', 'Admin deleted successfully.');
    }
    
}