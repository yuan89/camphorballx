<?php

namespace App\Models;

use App\Models\BaseModel;
use Symfony\Component\Console\Helper\Table;

class TableData extends BaseModel
{
    protected $table = 'tabledata';

    
	public static function create($tableDataJson)
	{
        $model = new TableData();
        $model->data = $tableDataJson;
        $insertId = $model->save();
        if ($insertId) {
            return $model->id;
        } else {
            return 0;
        }
	}

	public static function getTableDataById($id)
    {
        $model = TableData::find($id);
        return $model;
    }

}