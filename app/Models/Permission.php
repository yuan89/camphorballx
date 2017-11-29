<?php

namespace App\Models;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{

    public static function checkPermissionExits($name)
    {
        //检查permission是否存在
        $permissionExits = Permission::where('name', $name)->first();
        if ($permissionExits) {
            return true;
        } else {
            return false;
        }
    }

    public static function permissionDelete($id)
    {
        $result = Permission::find($id)->delete();
        return $result;
    }

    public static function getPermissionById($id)
    {
        $model = Permission::find($id);
        return $model;
    }

    public static function permissionsList()
    {
        $lists = Permission::all();
        $permissionLists = [];
        foreach ($lists as $k => $v) {
            $tmpLists = [];
            $tmpLists['id'] = $v['id'];
            $tmpLists['name'] = $v['name'];
            $tmpLists['description'] = $v['description'];
            $permissionLists[] = $tmpLists;
        }
        return $permissionLists;
    }

    public static function permissionNameList()
    {
        $lists = Permission::all()->toArray();
        $tmp = [];
        foreach ($lists as $k => $v) {
            $tmp[] = $v['name'];
        }
        return $tmp;
    }

    public static function permissionCreate($name, $description)
    {
        if ($name) {
            $permission = new Permission();
            $permission->name = $name;
            $permission->description = $description;
            $id = $permission->save();
            if ($id) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function permissionUpdate($model, $name, $description)
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

}
