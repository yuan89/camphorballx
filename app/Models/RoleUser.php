<?php

namespace App\Models;

use App\Models\BaseModel;


class RoleUser extends BaseModel
{
    protected $table = 'role_user';

    public static function getRoles($userId)
    {
        $results = RoleUser::where('user_id', $userId)->get()->toArray();
        $roleIds = [];
        foreach ($results as $key=>$val) {
            $roleIds[] = $val['role_id'];
        }
        return $roleIds;
    }

}
