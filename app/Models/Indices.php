<?php 
namespace App\Models;

use App\Models\BaseModel;

class Indices extends BaseModel
{
    protected $table = 'indices';

    public static function indicesDelete($id)
    {
        $result = Indices::find($id)->delete();
        return $result;
    }

    public static function indicesUpdate($model, $name, $description, $product_id)
    {
        $model->name = $name;
        $model->description = $description;
        $model->product_id = $product_id;

        $Id = $model->save();
        if ($Id) {
            return $model->id;
        } else {
            return 0;
        }
    }

    public static function indicesUpdateById($id, $indices_ids,$role_id)
    {
        $indiceModel = Indices::where('id', $id)->first();
        $role_id_string = $indiceModel->role_id;
        if ($role_id_string) {
            $roleIds = explode(",", $role_id_string);
        } else {
            $roleIds = [];
        }

        if ($indices_ids) {
            if (in_array($id, $indices_ids)) {
                if (!in_array($role_id, $roleIds)) {
                    array_push($roleIds, $role_id);
                    if (count($roleIds) > 1){
                        $role_id_string = implode(",", $roleIds);
                    } else {
                        $role_id_string = $role_id;
                    }
                }
            } else {
                $key = array_search($role_id, $roleIds);
                if ($key !== false) {
                    array_splice($roleIds, $key, 1);
                }
                if ($roleIds) {
                    $role_id_string = implode(",", $roleIds);
                } else {
                    $role_id_string = "";
                }
            }
        } else {
            $key = array_search($role_id, $roleIds);
            if ($key !== false) {
                array_splice($roleIds, $key, 1);
            }
            if ($roleIds) {
                $role_id_string = implode(",", $roleIds);
            } else {
                $role_id_string = "";
            }
        }

        $indiceModel->role_id = $role_id_string;
        $updateStatus = $indiceModel->save();
        if ($updateStatus) {
            return $updateStatus;
        } else {
            return 0;
        }

    }


    public static function getIndicesById($id)
    {
        $model = Indices::find($id);
        return $model;
    }

    public static function checkNameExits($name)
    {
        $Model = Indices::where('name', $name)->first();
        if ($Model) {
            return $Model->id;
        } else {
            return 0;
        }
    }

    public static function create($name, $description, $productId)
    {
        $model = new Indices();
        $model->name = $name;
        $model->description = $description;
        $model->product_id = $productId;
        $insertId = $model->save();
        if ($insertId) {
            return $insertId;
        } else {
            return 0;
        }
    }

    public static function indicesList($roleId)
    {
        $allIndices = Indices::all()->toArray();
        $tmpIndices = [];

        foreach ($allIndices as $indice) {
            $roleIds = $indice['role_id'];
            $indiceArray = explode(",", $roleIds);
            if (in_array($roleId, $indiceArray)) {
                $tmpIndices[] = $indice;
            }
        }

        return $tmpIndices;
    }

    public static function indicesTotalList()
    {
        return Indices::all()->toArray();
    }

    public static function getIndicesByProduct($id)
    {
        $productList = [];
        $allIndices = Indices::all()->toArray();
        foreach ($allIndices as $indices) {
            $productIdString = $indices['product_id'];
            if ($productIdString) {
                $productIdArray = explode(",", $productIdString);
                if (in_array($id, $productIdArray)) {
                    $productList[] = $indices;
                }
            }
        }

        return json_encode($productList);
    }

    public static function updateProductIdById($id, $productId, $delete)
    {

        $model = Indices::where('id', $id)->first();
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

}
