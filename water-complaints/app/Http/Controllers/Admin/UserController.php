<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
{
    $users = User::all(); 
    return view('admin.users.index', compact('users'));
}
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $departments = Department::all();

        return view('admin.users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->input('role');
        $user->department_id = $request->input('department_id');
        $user->save();

        return redirect()->route('admin.users.edit', $user->id)->with('success', 'User updated.');
    }

    public function destroy($id)
{
    $user = User::findOrFail($id);
    $user->delete();

    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
}

public function show($id)
{
    $user = User::findOrFail($id);
    return view('admin.users.show', compact('user'));
}


}
