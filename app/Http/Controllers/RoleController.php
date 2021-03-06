<?php

namespace App\Http\Controllers;

use App\Models\RoleHasPermissions;
use App\Models\User;
use App\Models\UserHasRoles;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller

{   
    public function index(){
        $roles  =  Role::get();
        
        return Inertia::render('Roles/Index', [
            'roles' => $roles
        ]);
    }

    public function create(){
        return Inertia::render('Roles/Create');
    }

    public function store(Request $request){
        
        $request->validate([
            'role'          => 'required|max:20',
            'display_name'  => 'required|max:50',
        ]);

        Role::create(['name' => $request->role, 'display_name' => $request->display_name, 'status' => $request->status]);
        
        return redirect('roles')->with(['type'=>'success', 'message'=>'Role added successfully !']);
    }

    public function edit(Request $request){
        $roles      =   Role::where('name', $request->role)->first();

        return Inertia::render('Roles/Edit', [
            'roles' => $roles
        ]);
    }

    public function update(Request $request){

        $request->validate([
            'display_name' => 'required|max:50',
        ]);

        DB::table('roles')
            ->where('name', $request->name)
            ->update(['display_name' => $request->display_name, 'status' => $request->status]);

        return redirect('roles')->with(['type'=>'success', 'message'=>'Role updated successfully !']);
    }

    public function destroy($id){
        
        DB::table('roles')->where('id', $id)->delete();
        UserHasRoles::where('role_id', $id)->delete();
        
        return redirect('roles')->with(['type'=>'success', 'message'=>'Role deleted successfully !']);
    }

    public function assignPermissions(Request $request)
    {
        $permissions        =   Permission::get();
        $role_permissions   =   RoleHasPermissions::where('role_id', $request->role_id)->get('permission_id')->keyBy('permission_id');

        return Inertia::render('Roles/AssignPermissions', [
            'role_id' => $request->role_id,
            'permissions' => $permissions,
            'role_permissions' => $role_permissions,
        ]);
    }

    public function assignPermissionsStore(Request $request)
    {
        RoleHasPermissions::where('role_id', $request->role_id)->delete();

        foreach($request->selected_permissions as $key=>$permission_id){
            DB::table('role_has_permissions')->insert([
                'role_id'   =>  $request->role_id,
                'permission_id'   =>  $permission_id
            ]);
        }

        return redirect('roles')->with(['type'=>'success', 'message'=>'Permissions assigned successfully !']);

    }
}
