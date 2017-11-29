<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Lib\DslLib;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketSelectorAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;

use GuzzleHttp\Client;

class ElasticsearchController extends Controller
{
    private $dslLib;


    public function initDSL($params = [])
    {
        $elasticsearchHost = getenv('ELASTICSEARCH_HOST');

        $hosts = [
            [
                'host' => $elasticsearchHost,    // Only host is required
                //'host' => '192.168.18.184',    // Only host is required
            ]
        ];
        $this->dslLib = new DslLib($params, $hosts);

    }

    public function update(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);

        $result = $this->dslLib->updateAll();
        if ($result) {
            return $this->Success("更新成功");
        } else {
            return $this->Error("更新失敗");
        }
    }


    public function versionRemain(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);

        $outputs = $this->dslLib->handleVersioRemain($params);

        $graph = isset($params['graph']) ? $params['graph'] : 0;
        $outputs['graph'] = $graph;
        $outputs['code'] = 0;

        return $outputs;
    }


    public function newMac(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);
        //初步分析结果
        $index = isset($params['index']) ? $params['index'] : '';
        $collapse = isset($params['collapse']['field']) ? $params['collapse']['field'] : "";
        if (empty($index)) {
            $this->Error("缺少索引");
        }
        $result = $this->dslLib->createSearch($params);

        $xAxis = isset($result['xAxis']) ? $result['xAxis'] : [];
        $outputs['xAxis'] = $xAxis;
        $numberList = [];
        foreach ($xAxis as $x) {
            $firstDay = $this->extractTime($x);
            $secondDay = date("Y-m-d", strtotime("1 days", strtotime($firstDay)));
            $firstDayTime = $firstDay."T00:00:00.000Z";
            $secondDayTime = $secondDay."T00:00:00.000Z";
            $newMac = $this->dslLib->handleNewMac($index, $firstDayTime, $secondDayTime, $collapse);
            $numberList[] = $newMac;
        }
        $graph = isset($params['graph']) ? $params['graph'] : 0;
        $outputs['graph'] = $graph;
        $outputs['code'] = 0;
        $tmpArray = $numberList;

        $extendsArray['data'] = $tmpArray;
        $extendsArray['extendsInfo'] = "";
        $extendsArray['label'] = "";

        $outputs['yAxis'][] = $extendsArray;

        return $outputs;
    }

    public function active(Request $request)
    {

        $params = $request->all();
        $this->initDSL($params);

        $space = isset($params['space']) ? $params['space'] : 0;

        //初步分析结果
        $result = $this->dslLib->createSearch($params);

        //print_r($result);exit;
        $xAxis = isset($result['xAxis']) ? $result['xAxis'] : [];
        $outputs['xAxis'] = $xAxis;
        $outputs['yAxis'] = [];
        $tmpArray = [];

        $allUserCount = $this->dslLib->getAllUser();

        foreach ($xAxis as $x) {
            //对每天进行分析
            $timeDay = $this->extractTime($x);
            $timeDay = date("Y-m-d", strtotime("1 days", strtotime($timeDay)));
            $currentTime = $timeDay."T00:00:00.000Z";

            unset($params['aggsX']);
            unset($params['query']);
            $activeCount = $this->dslLib->handleActive($params, $currentTime, $timeDay, $space);
            $tmpArray[] = $activeCount;
        }
        $extendsArray['data'] = $tmpArray;
        $extendsArray['extendsInfo'] = "";
        $extendsArray['label'] = "";

        $outputs['yAxis'][] = $extendsArray;

        $id = isset($params['id']) ? $params['id'] : 0;
        $graph = isset($params['graph']) ? $params['graph'] : 0;
        $outputs['id'] = $id;
        $outputs['graph'] = $graph;
        $outputs['code'] = 0;

        return $outputs;
    }

    public function lists(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);

        $pre = isset($params['pre']) ? $params['pre'] : null;
        $back = isset($params['back']) ? $params['back'] : null;
        $extends = isset($params['extends']) ? $params['extends'] : null;

        if ($pre) {
            if ($pre['func'] ==  "TermQuery") {
                $extendsQuerys = $this->dslLib->generatePre($pre);
                $total = $this->dslLib->createPreQuery($params, $extendsQuerys);
            } elseif($pre['func'] ==  "CardinalityAggregation") {
                $total = $this->dslLib->createPreAggregation($params);
            }

            if ($extends) {
                $tmpCalculations = [];
                foreach ($extends['calculations'] as $value) {
                    if ($value == "pre") {
                        $tmpCalculations[] = $total;
                    } else {
                        $tmpCalculations[] = $value;
                    }
                }
                $params['extends']['calculations'] = $tmpCalculations;
            }
        }

        $result = $this->dslLib->createSearch($params);

        $id = isset($params['id']) ? $params['id'] : 0;
        $graph = isset($params['graph']) ? $params['graph'] : 0;

        $result['id'] = $id;
        $result['graph'] = $graph;
        $result['code'] = 0;
        if ($back) {
            $backs = $this->dslLib->createBackAggregation($params);
            $result['back'] = $backs;
        }
        //return json_encode($result['aggregations']);

        return $result;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);

        $pre = isset($params['pre']) ? $params['pre'] : null;
        $back = isset($params['back']) ? $params['back'] : null;
        $extends = isset($params['extends']) ? $params['extends'] : null;
        if ($pre) {
            if ($pre['func'] ==  "TermQuery") {
                $extendsQuerys = $this->dslLib->generatePre($pre);
                $total = $this->dslLib->createPreQuery($params, $extendsQuerys);
            } elseif($pre['func'] ==  "CardinalityAggregation") {
                $total = $this->dslLib->createPreAggregation($params);
            }

            if ($extends) {
                $tmpCalculations = [];
                foreach ($extends['calculations'] as $value) {
                    if ($value == "pre") {
                        $tmpCalculations[] = $total;
                    } else {
                        $tmpCalculations[] = $value;
                    }
                }
                $params['extends']['calculations'] = $tmpCalculations;
            }
        }

        $result = $this->dslLib->createSearch($params);

        $id = isset($params['id']) ? $params['id'] : 0;
        $graph = isset($params['graph']) ? $params['graph'] : 0;
        $result['id'] = $id;
        $result['graph'] = $graph;
        $result['code'] = 0;
        if($back) {
            $result['back'] = $back;
        }

        return $result;
    }

    //统计留存
    /**
     * @param Request $request
     */
    public function remain(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);

        $index = isset($params['index']) ? $params['index'] : [];
        $number = isset($params['number']) ? $params['number'] : "";
        $from = isset($params['from']) ? $params['from'] : 0;

        if (empty($index)) {
            return $this->Error("缺少index");
        }

        //初步分析结果
        $result = $this->dslLib->createSearch($params);

        $space = isset($params['space']) ? $params['space'] : 0;
        $size = isset($params['size']) ? $params['size'] : 0;
        $index = isset($params['index']) ? $params['index'] : "";
        if (empty($index)) {
            return $this->Error("缺少index");
        }
        $result['aggregations']['space'] = $space;

        $outputs['xAxis'] = isset($result['xAxis']) ? $result['xAxis'] : [];

        $tmpArray = [];
        $tmpArray['extendsInfo'] = "";
        if ($outputs['xAxis']) {
            foreach ($result['xAxis'] as $k => $v) {
                $tmpTime = $v;
                $tmpDay = $this->extractTime($tmpTime);
                $tmpRemain = $this->dslLib->createRemain($tmpDay, $size, $from, $index, $space, []);
                $tmpArray['data'][] = isset($tmpRemain[$number]) ? $tmpRemain[$number] : '';
            }
            $outputs['yAxis'][] = $tmpArray;
        }

        $outputs['code'] = 0;

        return $outputs;
    }

    public function extractTime($tmpTime)
    {
        $day = substr($tmpTime, 0, 10);
        return $day;
    }

    public function mapping(Request $request)
    {
        $params = $request->all();
        $this->initDSL($params);

        $index = isset($params['index']) ? $params['index'] : '';
        if (empty($index)) {
            $this->Error("缺少索引");
        }

        $result = $this->dslLib->mapping($index);

        return $result;
    }

    public function catIndices()
    {
        $this->initDSL();
        $result = $this->dslLib->catIndices();
        $indices = [];
        foreach ($result as $k => $v) {
            $indices[] = $v['index'];
        }
        return json_encode($indices);
    }

}
