<?php

namespace App\Models;

use App\Models\BaseModel;

class Dashboard extends BaseModel
{
    protected $table = 'dashboard';

    public static function checkNameExits($name)
    {
        $Model = Dashboard::where('name', $name)->first();
        if ($Model) {
            return $Model->id;
        } else {
            return 0;
        }
    }

    public static function deleteById($id)
    {
        $model = Dashboard::find($id);
        if ($model) {
            $result = Dashboard::find($id)->delete();
            return $result;
        } else {
            return false;
        }
    }

    public static function getDashboardModelById($id)
    {
        $model = Dashboard::where('id', $id)->first();
        return $model;
    }

    public static function getDashboardById($id)
    {
        $visualizes = [];
        $model = Dashboard::where('id', $id)->first()->toArray();
        if ($model) {
            $visualize_ids = $model['visualize_ids'];
            if ($visualize_ids) {
                $ids = explode(",", $visualize_ids);

                foreach ($ids as $id) {
                    $tmp = Visualize::getVisualizeById($id);
                    $visualizes[] = $tmp;
                }
            }
        }

        return $visualizes;
    }


    public static function create($productId, $name, $description, $visualize_ids)
    {
        $model = new Dashboard();
        $model->name = $name;
        $model->product_id = $productId;
        $model->description = $description;
        $model->visualize_ids = $visualize_ids;

        $insertId = $model->save();
        if ($insertId) {
            return $insertId;
        } else {
            return 0;
        }
    }

    public static function dashboardUpdate($model, $name, $description, $visualize_ids, $productIds)
    {
        if ($name) {
            $model->name = $name;
        }
        if ($description) {
            $model->description = $description;
        }
        if ($visualize_ids) {
            $model->visualize_ids = $visualize_ids;
        }
        if ($productIds) {
            $model->product_id = $productIds;
        }
        $Id = $model->save();
        if ($Id) {
            return $model->id;
        } else {
            return 0;
        }
    }

    public static function getDashboardByRoleId($roleId)
    {
        $model = Dashboard::where('role_id', $roleId)->get()->toArray();
        return $model;
    }

    public static function getDashboards($username)
    {
        $userModel = User::getUserByName($username);
        $dashboards = [];
        if ($userModel) {
            $user_id = $userModel->id;
            $roleIds = RoleUser::getRoles($user_id);
            foreach ($roleIds as $key => $val) {
                $result = Dashboard::getDashboardByRoleId($val);
                $dashboards = array_merge($dashboards, $result);
            }
        }

        return json_encode($dashboards);
    }

    public static function getVisualizeIds()
    {
        return Dashboard::all(['id', 'visualize_ids'])->toArray();
    }

    public static function updateVisualIdsById($visualize_ids, $id)
    {
        $model = Dashboard::find($id);
        if ($model) {
            $model->visualize_ids = $visualize_ids;
            $Id = $model->save();
            if ($Id) {
                return $Id;
            } else {
                return 0;
            }
        }

    }

    public static function updateProductIdById($id, $productId, $delete)
    {
        $model = Dashboard::where('id', $id)->first();

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

    public static function getDashboardByProduct($id)
    {
        $productList = [];
        $allDashboard = Dashboard::all()->toArray();
        foreach ($allDashboard as $dashboard) {
            $productIdString = $dashboard['product_id'];
            if ($productIdString) {
                $productIdArray = explode(",", $productIdString);
                if (in_array($id, $productIdArray)) {
                    $productList[] = $dashboard;
                }
            }
        }

        return json_encode($productList);
    }
}