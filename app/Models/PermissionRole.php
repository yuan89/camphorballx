<?php

namespace App\Models;

use App\Models\BaseModel;


class PermissionRole extends BaseModel
{
    protected $table = 'permission_role';

    public static function getPermissions($roleId)
    {
        $results = PermissionRole::where('role_id', $roleId)->get()->toArray();
        $permissionIds = [];
        foreach ($results as $key=>$val) {
            $permissionIds[] = $val['permission_id'];
        }
        return $permissionIds;
    }

}
