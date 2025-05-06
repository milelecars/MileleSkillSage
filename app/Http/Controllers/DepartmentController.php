<?php

namespace App\Http\Controllers;


use App\Models\Department;
use Illuminate\Http\Request;


class DepartmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $department = Department::create(['name' => $validated['name']]);

        return response()->json(['id' => $department->id, 'name' => $department->name]);
    }

    public function search(Request $request)
    {
        $search = $request->q;

        $departments = Department::where('name', 'like', "%{$search}%")
                        ->orderBy('name')
                        ->get(['name']);

        return response()->json($departments);
    }
}