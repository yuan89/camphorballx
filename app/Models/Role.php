<?php

namespace App\Models;

use DB;
use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{

    public static function roleDelete($id)
    {
        $model = Role::find($id);
        if ($model) {
            $result = Role::find($id)->delete();
            return $result;
        } else {
            return false;
        }
    }

    public static function getRoleById($id)
    {
        $model = Role::find($id);
        return $model;
    }

    public static function checkRoleExits($role)
    {
        $roleModel = Role::where('name', $role)->first();
        if ($roleModel) {
            return $roleModel->id;
        } else {
            return 0;
        }
    }

    public static function roleCreate($name, $description)
    {
        if ($name) {
            $owner = new Role();
            $owner->name = $name;
            $owner->description = $description;
            $id = $owner->save();
            if ($id) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function roleUpdate($model, $name, $description)
    {
        $model->name = $name;
        $model->description = $description;
        $result = $model->save();
        if ($result) {
            return $result;
        } else {
            return 0;
        }
    }



    public static function rolePermissions($name)
    {
        $roleModel = Role::where('name', $name)->first();
        $rolePermissions = [];
        if ($roleModel) {
            $rid = $roleModel->id;
            $model = DB::select("select id,name,description from permission_role  a left join permissions b on a.permission_id=b.id where a.role_id = $rid");
            foreach ($model as $k => $v) {
                $tmp = [];
                $tmp['name'] = $v->name;
                $tmp['id'] = $v->id;
                $tmp['description'] = $v->description;
                $rolePermissions[] = $tmp;
            }
        } else {
            return false;
        }

        return $rolePermissions;
    }

    public static function roleNameList()
    {
        $lists = Role::all()->toArray();
        $tmp = [];
        foreach ($lists as $k => $v) {
            $tmp[] = $v['name'];
        }
        return $tmp;
    }

    public static function rolesList()
    {
        $lists = Role::all();
        $roleLists = [];
        foreach ($lists as $k => $v) {
            $tmpLists = [];
            $tmpLists['id'] = $v['id'];
            $tmpLists['name'] = $v['name'];
            $tmpLists['description'] = $v['description'];
            $roleLists[] = $tmpLists;
        }

        return $roleLists;
    }

    public static function roleAttachPermission($role, $permissions)
    {
        $permissionNameList = Permission::permissionNameList();
        $roleModel = Role::where('name', $role)->first();
        foreach ($permissions as $key => $val) {
            $permissionModel = Permission::where('name', $val)->first();
            if ($permissionModel) {
                $pid = $permissionModel->id;
                $permissionExits = $roleModel->hasPermission($val);
                if (empty($permissionExits)) {
                    $roleModel->attachPermission($pid);
                }
            } else {
                return false;
            }
        }

        foreach ($permissionNameList as $m => $n) {
            if (!in_array($n, $permissions)) {
                $permissionModel = Permission::where('name', $n)->first();
                if ($permissionModel) {
                    $pid = $permissionModel->id;
                    $permissionExits = $roleModel->hasPermission($val);
                    if ($permissionExits) {
                        $roleModel->detachPermission($pid);
                    }
                } else {
                    return false;
                }
            }
        }

        return true;

    }
}
