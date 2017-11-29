<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Indices;
use App\Models\Role;
use App\Models\Product;
use App\Models\Visualize;
use App\Models\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Lib\DslLib;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;


class panelController extends Controller
{

    public function __construct()
    {
        //
    }


    public function  visualizeList(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['dashboard_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $dashboardId = $params['dashboard_id'];
        $visualizes = Visualize::getVisualizes($dashboardId);

        return json_encode($visualizes);
    }

    public function productCreate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['name', 'description']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $name = $params['name'];
        $description = $params['description'];
        $parent = $params['parent'];
        $role_id = $params['role_id'];

        $productExist = Product::checkProductExits($name);
        if ($productExist) {
            return $this->Error("產品已經存在");
        }
        $insertStatus = Product::productCreate($name, $description, $parent, $role_id);
        if ($insertStatus) {
            return $this->Success("新增產品成功");
        } else {
            return $this->Error("新增產品失敗");
        }
    }

    public function productUpdate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id', 'name', 'description', 'role_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id  = $params['id'];
        $name = $params['name'];
        $parent = $params['parent'];
        $description = $params['description'];
        $role_id = $params['role_id'];

        $productModel = Product::getProductById($id);
        if (!$productModel) {
            return $this->Error("產品不存在");
        }

        $updateStatus = Product::productUpdate($productModel, $name, $description, $parent, $role_id);
        if ($updateStatus) {
            return $this->Success("更新產品成功");
        } else {
            return $this->Error("更新產品失敗");
        }
    }

    public function productDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $id = $params['id'];
        $model = product::getProductById($id);
        if ($model) {
            $result = Product::productDelete($model->id);
            if ($result) {
                return $this->Success("删除成功");
            } else {
                return $this->Error("删除失败");
            }
        } else {
            return $this->Error("删除目标不存在");
        }
    }

    public function productTotalList()
    {
        return Product::productTotalList();
    }

    public function productList(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['username']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $username = $params['username'];
        $roles = User::userRoles($username);

        $result = [];
        foreach ($roles as $role) {
            $roleId = $role['id'];
            $tmpResult = Product::productList($roleId);
            $result = array_merge($result, $tmpResult);
        }

        return $result;
    }

    public function productAttachRole(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id', 'role_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id = $params['id'];
        $role_id = $params['role_id'];

        $productModel = Product::getProductById($id);
        if ($productModel) {
            $updateStatus = Product::productAttachRole($productModel, $role_id);
            if ($updateStatus) {
                return $this->Success("更新成功");
            } else {
                return $this->Error("更新失敗");
            }
        } else {
            return $this->Error("產品不存在");
        }
    }

    public function productAttachIndex(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $indicesIds = $params['indices_ids'];
        $allIndices = Indices::all(['id'])->toArray();

        $indicesIdsList = explode(",", $indicesIds);

        foreach ($allIndices as $indices) {
            $indicesId = $indices['id'];
            if ($indicesIdsList) {
                if (in_array($indicesId, $indicesIdsList)) {
                    Indices::updateProductIdById($indicesId, $productId, false);
                } else {
                    Indices::updateProductIdById($indicesId, $productId, true);
                }
            } else {
                Indices::updateProductIdById($indicesId, $productId, true);
            }
        }

        return $this->Success("关联成功");
    }


    public function productAttachVisualize(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $visualizeIds = $params['visualize_ids'];
        $allVisualizes = Visualize::all(['id'])->toArray();

        $visualizesIdsList = explode(",", $visualizeIds);

        foreach ($allVisualizes as $visualize) {
            $visualizeId = $visualize['id'];
            if ($visualizesIdsList) {
                if (in_array($visualizeId, $visualizesIdsList)) {
                    Visualize::updateProductIdById($visualizeId, $productId, false);
                } else {
                    Visualize::updateProductIdById($visualizeId, $productId, true);
                }
            } else {
                Visualize::updateProductIdById($visualizeId, $productId, true);
            }
        }

        return $this->Success("关联成功");
    }

    public function productAttachDashboard(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $dashboardIds = $params['dashboard_ids'];
        $allDashboards = Dashboard::all(['id'])->toArray();

        $dashboradsIdsList = explode(",", $dashboardIds);

        foreach ($allDashboards as $visualize) {
            $dashboradId = $visualize['id'];
            if ($dashboradsIdsList) {
                if (in_array($dashboradId, $dashboradsIdsList)) {
                    Dashboard::updateProductIdById($dashboradId, $productId, false);
                } else {
                    Dashboard::updateProductIdById($dashboradId, $productId, true);
                }
            } else {
                Dashboard::updateProductIdById($dashboradId, $productId, true);
            }
        }

        return $this->Success("关联成功");
    }

    public function productAttachUser(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $userIds = $params['user_ids'];
        $allUsers = User::all(['id'])->toArray();

        $usersIdsList = explode(",", $userIds);

        foreach ($allUsers as $user) {
            $userId = $user['id'];
            if ($usersIdsList) {
                if (in_array($userId, $usersIdsList)) {
                    User::updateUserIdById($userId, $productId, false);
                } else {
                    User::updateUserIdById($userId, $productId, true);
                }
            } else {
                User::updateUserIdById($userId, $productId, true);
            }
        }

        return $this->Success("关联成功");
    }


    public function dashboardDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $id = $params["id"];
        $result = Dashboard::deleteById($id);

        if ($result) {
            return $this->Success("删除成功");
        } else {
            return $this->Error("删除失败");
        }
    }

    public function visualizeDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $id = $params["id"];
        $result = Visualize::deleteById($id);


        if ($result) {
            //删除visualize的同时，删除dashboard中包含的visualize_ids
            $visualizeIds = Dashboard::getVisualizeIds();
            foreach ($visualizeIds as $k => $v) {
                $ids = $v["visualize_ids"];
                $tmpId = $v["id"];
                $tmpArray = explode(",", $ids);
                $key=array_search($id ,$tmpArray);
                if (is_numeric($key)) {
                    array_splice($tmpArray, $key, 1);
                    $tmpString = implode(",", $tmpArray);
                    Dashboard::updateVisualIdsById($tmpString, $tmpId);
                }
            }

            return $this->Success("删除成功");
        } else {
            return $this->Error("删除失败");
        }
    }

    public function visualizeCreate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['username', 'product_id', 'name', 'type', 'template']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $name = $params['name'];
        $description = $params['description'];
        $type = $params['type'];
        $template = json_encode($params['template']);


        $visualizId = Visualize::checkNameExits($name);
        if (!$visualizId) {
            $insertId = Visualize::create($productId, $name, $description, $type, $template);
            if ($insertId) {
                $extends['id'] = $insertId;
                return $this->Success("新增Visualize成功", $extends);
            }
        } else {
            return $this->Error("name重复");
        }
    }

    public function visualizeUpdate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id = $params['id'];
        $name = isset($params['name']) ? $params['name'] : "";
        $description = isset($params['description']) ? $params['description'] : "";
        $type = isset($params['type']) ? $params['type'] : "";
        $template = isset($params['template']) ? $params['template'] : "";
        $productIds = isset($params['product_id']) ? $params['product_id'] : "";

        $model = Visualize::getVisualizeById($id);
        if ($model) {
            $updateId = Visualize::updateVisualize($model, $name, $description, $type, $template, $productIds);
            if ($updateId) {
                $extends['id'] = $updateId;
                return $this->Success("更新成功", $extends);
            } else {
                return $this->Error("更新失败");
            }
        } else {
            return $this->Error("id对应数据不存在");
        }
    }

    public function getDashboard(Request $request, $id)
    {
        if ($id && is_numeric($id)) {
            Dashboard::getDashboardById($id);
        } else {
            return $this->Error("参数ID错误");
        }

        $dashboard = Dashboard::getDashboardById($id);

        return $dashboard;
    }

    public function dashboardList(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['username']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $username = $params['username'];
        $dashboards = Dashboard::getDashboards($username);

        return $dashboards;
    }

    public function dashboardCreate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['username', 'product_id', 'name', 'visualize_ids']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $name = $params['name'];
        $description = $params['description'];
        $visualize_ids = $params['visualize_ids'];

        //$roleId = Role::checkRoleExits($role);
        //if (!$roleId) {
        //    return $this->Error("role不存在");
        //}

        $dashboardId = Dashboard::checkNameExits($name);
        if (!$dashboardId) {
            $insertId = Dashboard::create($productId, $name, $description, $visualize_ids);
            if ($insertId) {
                return $this->Success("新增Dashboard成功");
            } else {
                return $this->Error("新增Dashboard失败");
            }
        } else {
            return $this->Error("name重复");
        }
    }

    public function dashboardUpdate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $id = $params['id'];
        $name = isset($params['name'])? $params['name'] : "";
        $description = isset($params['description']) ? $params['description'] : "";
        $visualize_ids = isset($params['visualize_ids']) ? $params['visualize_ids'] : "";
        $productIds = isset($params['product_id']) ? $params['product_id'] : "";

        $dashboardModel = Dashboard::getDashboardModelById($id);
        if ($dashboardModel) {
            $updateId = Dashboard::dashboardUpdate($dashboardModel, $name, $description, $visualize_ids, $productIds);
            if ($updateId) {
                return $this->Success("更新Dashboard成功");
            } else {
                return $this->Error("更新Dashboard失败");
            }
        } else {
            return $this->Error("id对应数据不存在");
        }
    }

    public function indicesCreate(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['name']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $name = $params['name'];
        $description = isset($params['description']) ? $params['description'] : '';
        $productId = isset($params['product_id']) ? $params['product_id'] : '';

        $indicesId = Indices::checkNameExits($name);

        if ($indicesId) {
            return $this->Error("索引已经存在");
        } else {
            $insertId = Indices::create($name, $description, $productId);
            if ($insertId) {
                return $this->Success("索引新建成功");
            } else {
                return $this->Error("索引新建失败");
            }
        }
    }

    public function indicesUpdate(Request $request)
    {
        $params = $request->all();
        $id = isset($params['id']) ? $params['id'] : 0;
        if (empty($id)){
            return $this->Error("id不能为空");
        }

        $checkParamsExits = $this->checkParamsExits($params, ['name']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $name = $params['name'];
        $description = isset($params['description']) ? $params['description'] : '';
        $product_id = isset($params['product_id']) ? $params['product_id'] : '';
        //$role_id = isset($params['role_id']) ? $params['role_id'] : '';

        $model = Indices::getIndicesById($id);

        if ($model) {
            $updateId = Indices::indicesUpdate($model, $name, $description, $product_id);
            if ($updateId) {
                return $this->Success("更新成功");
            } else {
                return $this->Error("更新失败");
            }
        } else {
            return $this->Error("id对应数据不存在");
        }

    }


    public function indicesRUpdate(Request $request)
    {
        $params = $request->all();

        $checkParamsExits = $this->checkParamsExits($params, ['role_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        if ($params['indice_ids']) {
            $indices_ids = explode(",", $params['indice_ids']);
        } else {
            $indices_ids = [];
        }

        $role_id = $params['role_id'];

        $tmpIds = Indices::all(['id'])->toArray();
        $ids = [];
        foreach ($tmpIds as $tmpId) {
            $ids[] = $tmpId['id'];
        }

        foreach ($ids as $id) {
            $updateStatus = Indices::indicesUpdateById($id, $indices_ids,$role_id);
        }

        return $this->Success("更新成功");
    }


    public function indicesDelete(Request $request)
    {
        $params = $request->all();
        $checkParamsExits = $this->checkParamsExits($params, ['id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $id = $params['id'];
        $model = Indices::getIndicesById($id);
        if ($model) {
            $result = Indices::indicesDelete($model->id);
            if ($result) {
                return $this->Success("删除成功");
            } else {
                return $this->Error("删除失败");
            }
        } else {
            return $this->Error("删除目标不存在");
        }
    }

    public function indicesTotalList()
    {
        return Indices::indicesTotalList();
    }


    public function indicesList(Request $request)
    {
        $params = $request->all();

        $checkParamsExits = $this->checkParamsExits($params, ['username']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }
        $username = $params['username'];
        $roles = User::userRoles($username);

        $result = [];
        foreach ($roles as $role) {
            $roleId = $role['id'];
            $tmpResult = Indices::indicesList($roleId);
            $result = array_merge($result, $tmpResult);
        }

        return $result;
    }

    public function getIndicesByProduct(Request $request)
    {
        $params = $request->all();

        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $productArray = Indices::getIndicesByProduct($productId);

        return $productArray;
    }

    public function getVisualizeByProduct(Request $request)
    {
        $params = $request->all();

        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $productArray = Visualize::getVisualizeByProduct($productId);

        return $productArray;
    }

    public function getDashboardByProduct(Request $request)
    {
        $params = $request->all();

        $checkParamsExits = $this->checkParamsExits($params, ['product_id']);
        if ($checkParamsExits == false) {
            return $this->Error("缺少参数");
        }

        $productId = $params['product_id'];
        $productArray = Dashboard::getDashboardByProduct($productId);

        return $productArray;
    }

}
