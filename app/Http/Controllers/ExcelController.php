<?php

namespace App\Http\Controllers;

use App\Models\TableData;
use Excel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExcelController extends Controller
{


    public function generate(Request $request)
    {
        $params = $request->all();
        $result = $this->handleExcel($params);

        return $result;
    }

    public function generateInside($params)
    {
        $this->handleExcel($params);
    }

    public function handleExcel($params)
    {
        $tableData = isset($params['tableData']) ? $params['tableData'] : '';
        $tableName = isset($params['tableName']) ? $params['tableName'] : '';
        if (empty($tableData)) {
            return $this->Error("tableData不存在");
        }
        if (empty($tableName)) {
            return $this->Error("tableName不存在");
        }

        $tableDataJson = json_encode($params);
        $result = TableData::create($tableDataJson);
        if ($result) {
            $extends['export_id'] = $result;
            return $this->Success("tableData保存成功", $extends);
        } else {
            return $this->Error("tableData保存失败");
        }

        return $result;
    }


    public function export(Request $request, $id)
    {
        $params = $request->all();
        $id = isset($id) ? $id : 0;
        if (empty($id)) {
            $this->Error('id不存在，请检查');
        }

        if (TableData::getTableDataById($id)) {
            $modelData = TableData::getTableDataById($id)->toArray();
        }

        $tableData = isset($modelData['data']) ? $modelData['data'] : '';
        if (empty($tableData)) {
            return $this->Error('数据为空');
        }

        $tableData = json_decode($tableData, 1);
        $tableName = isset($tableData['tableName']) ? $tableData['tableName'] : '';
        $tableData = isset($tableData['tableData']) ? $tableData['tableData'] : '';
        if (empty($tableName)) {
            return $this->Error('tableName为空');
        }
        if (empty($tableData)) {
            return $this->Error('tableData为空');
        }

        $pureTableData = [];
        foreach ($tableData as $val) {
            $pureTableData[] = $val;
        }

        $cellData = $tableData;
        //print_r($cellData);exit;
        Excel::create($tableName,function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->download('csv', $headers = ["Access-Control-Allow-Origin" => "*"]);

    }
}
