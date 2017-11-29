<?php

namespace App\Models;

use App\Models\BaseModel;

class Visualize extends BaseModel
{
    protected $table = 'visualize';

    public static function checkNameExits($name)
    {
        $visualizeModel = Visualize::where('name', $name)->first();
        if ($visualizeModel) {
            return $visualizeModel->id;
        } else {
            return 0;
        }
    }


    public static function create($productId, $name, $description, $type, $template)
    {
        $model = new Visualize();
        $model->product_id = $productId;
        $model->name = $name;
        $model->description = $description;
        $model->type = $type;
        $model->template = $template;

        $insertId = $model->save();
        $insertId = $model->id;
        if ($insertId) {
            return $insertId;
        } else {
            return 0;
        }
    }

    public static function getVisualizeById($id)
    {
        $model = Visualize::find($id);
        return $model;
    }

    public static function updateVisualize($model, $name, $description, $type, $template, $productIds)
    {
        if ($name) {
            $model->name = $name;
        }
        if ($description) {
            $model->description = $description;
        }
        if ($type) {
            $model->type = $type;
        }
        if ($template) {
            $model->template = json_encode($template);
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

    public static function getVisualizeByRoleId($roleId)
    {
        $model = Visualize::where('role_id', $roleId)->get()->toArray();
        return  $model;
    }

    public static function getVisualizes($dashboardId)
    {
        $result = [];
        $model = Dashboard::where('id', $dashboardId)->first();
        $visualizeIdsString = $model->visualize_ids;
        $visualizeIdsArray = explode(",", $visualizeIdsString);
        foreach ($visualizeIdsArray as $id) {
            if ($id) {
                $tmpModel = Visualize::getVisualizeById($id);
                if ($tmpModel) {
                    $result[] = $tmpModel;
                }

            }
        }

        return $result;
    }


    public static function deleteById($id)
    {
        $model = Visualize::find($id);
        if ($model) {
            $result = Visualize::find($id)->delete();
            return $result;
        } else {
            return false;
        }
    }

    public static function updateProductIdById($id, $productId, $delete)
    {
        $model = Visualize::where('id', $id)->first();

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

    public static function getVisualizeByProduct($id)
    {
        $productList = [];
        $allVisualize = Visualize::all()->toArray();
        foreach ($allVisualize as $visualize) {
            $productIdString = $visualize['product_id'];
            if ($productIdString) {
                $productIdArray = explode(",", $productIdString);
                if (in_array($id, $productIdArray)) {
                    $productList[] = $visualize;
                }
            }
        }

        return json_encode($productList);
    }

}