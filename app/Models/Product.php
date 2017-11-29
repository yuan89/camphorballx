<?php 
namespace App\Models;

use App\Models\BaseModel;

class Product extends BaseModel
{
    protected $table = 'product';

    public static function productDelete($id)
    {
        $result = Product::find($id)->delete();
        return $result;
    }

    public static function productCreate($name, $description, $parent = 0, $role_id = "")
    {
        $model = new Product();
        $model->name = $name;
        $model->parent = $parent;
        $model->description = $description;
        $insertId = $model->save();
        if ($insertId) {
            return $insertId;
        } else {
            return 0;
        }
    }

    public static function productUpdate($model, $name, $description, $parent = 0, $role_id = "")
    {
        $model->name = $name;
        $model->description = $description;
        $model->parent = $parent;
        $model->role_id = $role_id;
        $updateStatus = $model->save();
        if ($updateStatus) {
            return $updateStatus;
        } else {
            return 0;
        }
    }

    public static function productAttachRole($model, $role_id)
    {
        $model->role_id = $role_id;
        $updateStatus = $model->save();
        if ($updateStatus) {
            return $updateStatus;
        } else {
            return 0;
        }
    }

    public static function checkProductExits($name)
    {
        $productModel = Product::where('name', $name)->first();
        if ($productModel) {
            return $productModel->id;
        } else {
            return 0;
        }
    }

    public static function getProductById($id)
    {
        $model = Product::find($id);
        return $model;
    }


    public static function productList($roleId)
    {
        $allProducts = Product::all()->toArray();
        $tmpProducts = [];

        foreach ($allProducts as $product) {
            $roleIds = $product['role_id'];
            $productArray = explode(",", $roleIds);
            if (in_array($roleId, $productArray)) {
                $tmpProducts[] = $product;
            }
        }

        return $tmpProducts;
    }

    public static function productTotalList()
    {
        $allProducts = Product::all()->toArray();
        return $allProducts;
    }

}
