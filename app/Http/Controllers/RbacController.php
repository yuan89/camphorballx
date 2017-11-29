<?php

namespace App\Http\Controllers;

use App\Models\PermissionRole;
use App\Models\RoleUser;
use DB;
use App\Models\Car;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Lib\DslLib;
use Illuminate\Http\Request;

class RbacController extends Controller
{
    public function __construct()
    {
        //
    }


    public function rolePermissions(Request $request)
    {
        $params = $request->all();
        $name = isset($params['role']) ? $params['role'] : '';
        $permissions = Role::rolePermissions($name);

        return json_encode($permissions);
    }

    public function userRoles(Request $request)
    {
        $params = $request->all();
        $username = isset($params['username']) ? $params['username'] : '';
        $roles = User::userRoles($username);

        return json_encode($roles);
    }


    public function permissionsList(Request $request)
    {
        return json_encode(Permission::permissionsList());
    }

    public function rolesList(Request $request)
    {
        $roleList = Role::rolesList();

        return json_encode($roleList);
    }


    public function permissionCreate(Request $request)
    {
        $params = $request->all();

        $name = isset($params['name']) ? $params['name'] : '';
        $description = isset($params['description']) ? $params['description'] : '';

        $checkPermissionExits = Permission::checkPermissionExits($name);
        if ($checkPermissionExits) {
            return $this->Error("permission已经存在");
        }

        $result = Permission::permissionCreate($name, $description);
        if ($result) {
            return $this->Success("permission新增成功");
        } else {
            return $this->Error("permission新增失败");
        }
    }

    public function  permissionUpdate(Request $request)
    {
        $params = $request->all();
        $id = isset($params['id']) ? $params['id'] : 0;
        if (empty($id)) {
            return $this->Error("id错误");
        }
        $model = Permission::getPermissionById($id);
        if ($model) {

            $name = isset($params['name']) ? $params['name'] : '';
            $description = isset($params['description']) ? $params['description'] : '';
            $result = Permission::permissionUpdate($model, $name, $description);
            if ($result) {
                return $this->Success("更新成功");
            } else {
                return $this->Error("更新失败");
            }
        } else {
            return $this->Error("id对应的数据不存在");
        }

    }

    public function attachRole(Request $request)
    {
        $params = $request->all();
        $username = isset($params['username']) ? $params['username'] : '';
        $roles = isset($params['roles']) ? $params['roles'] : '';
        if (empty($username) || empty($roles)) {
            return $this->Error("缺少参数");
        }

        $result =  User::userAttachRole($username, $roles);
        if ($result) {
            return $this->Success("分配成功");
        } else {
            return $this->error("分配失败");
        }
    }

    public function attachPermission(Request $request)
    {
        $params = $request->all();

        $role = isset($params['role']) ? $params['role'] : '';
        $permissions = isset($params['permissions']) ? $params['permissions'] : '';

        if (empty($role) || empty($permissions)) {
            return $this->Error("缺少参数");
        }

        $result = Role::roleAttachPermission($role, $permissions);
        if ($result) {
            return $this->Success("权限归属成功");
        } else {
            return $this->Error("权限归属失败");
        }
    }


    public function roleCreate(Request $request)
    {
        $params = $request->all();
        $name = isset($params['name']) ? $params['name'] : '';
        $description = isset($params['description']) ? $params['description'] : '';

        //检查name是否存在
        $roleExits = Role::where('name', $name)->first();
        if ($roleExits) {
            return $this->Error("role已存在");
        }

        $result = Role::roleCreate($name, $description);
        if ($result) {
            return $this->Success("角色新增成功");
        } else {
            return $this->Error("角色新增失败");
        }
    }

    public function userPermissions(Request $request)
    {
        $params = $request->all();
        $username = isset($params['username']) ? $params['username'] : '';
        if(empty($username)) {
            return $this->Error("username不存在");
        }
        $userModel = User::getUserByName($username);
        $permissionModels = [];
        if ($userModel) {
            $userId = $userModel->id;
            $roleIds = RoleUser::getRoles($userId);
            foreach ($roleIds as $roleId) {
                $permissionIds = PermissionRole::getPermissions($roleId);
                foreach ($permissionIds as $permissionId) {
                    $model = Permission::getPermissionById($permissionId);
                    $permissionModels[] = $model->toArray();
                }
            }
        } else {
            return $this->Error("用户不存在");
        }

        return json_encode($permissionModels);
    }

    public function  roleUpdate(Request $request)
    {
        $params = $request->all();
        $id = isset($params['id']) ? $params['id']: 0;
        if (empty($id)) {
            return $this->Error("id错误");
        }
        $model = Role::getRoleById($id);
        if ($model) {
            $params = $request->all();
            $name = isset($params['name']) ? $params['name'] : '';
            $description = isset($params['description']) ? $params['description'] : '';
            $result = Role::roleUpdate($model, $name, $description);
            if ($result) {
                return $this->Success("更新成功");
            } else {
                return $this->Error("更新失败");
            }
        } else {
            return $this->Error("id对应的数据不存在");
        }

    }

    public function userHasPermission(Request $request)
    {
        $params = $request->all();
        $username = isset($params['username']) ? $params['username'] : '';
        $permission = isset($params['permission']) ? $params['permission'] : '';
        if (empty($username)) {
            return $this->Error("用户不存在");
        }
        if (empty($permission)) {
            return $this->Error("权限不存在");
        }
        return User::userHasPermission($username, $permission);
    }

    public function userList()
    {
        $userList = User::userList();

        return json_encode($userList);
    }

    public function userDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id = $params['id'];
        $model = User::getUserById($id);
        if ($model) {
            $result = User::userDelete($model->id);
            if ($result) {
                return $this->Success("删除成功");
            } else {
                return $this->Error("删除失败");
            }
        } else {
            return $this->Error("删除目标不存在");
        }
    }

    public function roleDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id = $params['id'];
        $model = Role::getRoleById($id);
        if ($model) {
            $result = Role::roleDelete($model->id);
            if ($result) {
                return $this->Success("删除成功");
            } else {
                return $this->Error("删除失败");
            }
        } else {
            return $this->Error("删除目标不存在");
        }
    }

    public function permissionDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id = $params['id'];
        $model = Permission::getPermissionById($id);
        if ($model) {
            $result = Permission::permissionDelete($model->id);
            if ($result) {
                return $this->Success("删除成功");
            } else {
                return $this->Error("删除失败");
            }
        } else {
            return $this->Error("删除目标不存在");
        }
    }


}
