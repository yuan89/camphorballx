<?php

namespace App\Models;

use DB;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use App\Models\BaseModel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    use EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'api_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public static function userUpdate($userModel, $password)
    {
        $userModel->password = $password;
        $updateResult = $userModel->save();

        return $updateResult;
    }

    public static function userDelete($id)
    {
        $result = User::find($id)->delete();
        return $result;
    }

    public static function getUserById($id)
    {
        $model = User::find($id);
        return $model;
    }

    public static function getUserByName($username)
    {
        $userModel = User::where('username', $username)->first();
        if ($userModel) {
            return $userModel;
        } else{
            return null;
        }
    }

    public static function getUserByEmail($email)
    {
        $model = User::where('email', $email)->first();
        if ($model) {
            return $model;
        } else{
            return null;
        }
    }

    public static function getUserByNamePassword($username, $password)
    {
        $userModel = User::where(['username' => $username,'password' => $password])->first();
        if ($userModel) {
            return $userModel;
        } else{
            return null;
        }
    }


    public static function userRoles($username)
    {
        $userModel = User::where('username', $username)->first();
        $userRoles = [];
        if ($userModel) {
            $uid = $userModel->id;
            $model = DB::select("select id,name,description from role_user a left join roles b on a.role_id=b.id where a.user_id = $uid");
            foreach ($model as $k => $v) {
                $tmpRole = [];
                $tmpRole['id'] = $v->id;
                $tmpRole['name'] = $v->name;
                $tmpRole['description'] = $v->description;
                $userRoles[] = $tmpRole;
            }
        } else {
            return false;
        }
        return $userRoles;
    }

    public static function userAttachRole($username, $roles)
    {
        $nameList = Role::roleNameList();
        $userModel = User::where('username', $username)->first();
        foreach ($roles as $key => $val) {
            $roleModel = Role::where('name', $val)->first();
            if ($roleModel) {
                $rid = $roleModel->id;
                $roleExits = $userModel->hasRole($val);
                if (empty($roleExits)) {
                    $userModel->attachRole($rid);
                }
            } else {
                return false;
            }
        }

        foreach ($nameList as $m => $n) {
            if (!in_array($n, $roles)) {
                $roleModel = Role::where('name', $n)->first();
                if ($roleModel) {
                    $rid = $roleModel->id;
                    $roleExits = $userModel->hasRole($n);
                    if ($roleExits) {
                        $userModel->detachRole($rid);
                    }
                }else {
                    return false;
                }
            }
        }
        return true;
    }


    public static function userHasPermission($username, $permission)
    {
        $userModel = User::where('username', $username)->first();
        $hasPermission = $userModel->encan($permission);
        if ($hasPermission) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function userList()
    {
        return User::all(['id', 'username', 'email'])->toArray();
    }

    public static function getProductByUserName($username)
    {
        $model = User::where('username', $username)->first();
        $productIdsString = $model->product_id;
        $productIdsArray = explode(",", $productIdsString);
        $productIdsArray = array_unique($productIdsArray);

        $childProductList = [];
        foreach ($productIdsArray as $productId) {
            $childProduct = Product::getProductById($productId);
            if ($childProduct && $childProduct->parent > 0) {
                $childProductList[] = $childProduct->toArray();
            }
        }

        $parentIds = [];
        foreach ($childProductList as $childProduct) {
            $parent = $childProduct['parent'];
            if (!in_array($parent, $parentIds)) {
                array_push($parentIds, $parent);
            }
        }

        $parentProductList = [];
        foreach ($parentIds as $parentId) {
            $parentProduct = Product::getProductById($parentId);
            if ($parentProduct) {
                $parentProductList[] = $parentProduct->toArray();
            }
        }

        $allProduct = array_merge($childProductList,$parentProductList);

        return json_encode($allProduct);
    }


    public static function updateUserIdById($id, $productId, $delete)
    {
        $model = User::where('id', $id)->first();

        $productIdString = $model->product_id;
        $productArray = [];

        if ($delete == false) {
            if ($productIdString) {
                $productArray = explode(",", $productIdString);
                if (!in_array($productId, $productArray)) {
                    array_push($productArray, $productId);
                }
            } else {
                $productArray[] = $productId;
            }
        } else {
            if ($productIdString) {
                $productArray = explode(",", $productIdString);
                $key = array_search($productId, $productArray);
                if ($key !== false) {
                    array_splice($productArray, $key, 1);
                }
            }
        }

        $productIdString = implode(",", $productArray);
        $model->product_id = $productIdString;
        $updateStatus = $model->save();
        if ($updateStatus) {
            return true;
        } else {
            return false;
        }

    }

    public static function userProduct($userId, $productIds)
    {
        $model = User::where('id', $userId)->first();
        $model->product_id = $productIds;
        $updateStatus = $model->save();

        return $updateStatus;
    }

}
