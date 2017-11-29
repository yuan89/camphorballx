<?php

namespace App\Lib;

use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketSelectorAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ONGR\ElasticsearchDSL\Collapse\FieldCollapse;
use ONGR\ElasticsearchDSL\Aggregation\Metric\CardinalityAggregation;

use App\Models\TableData;
use Excel;

class DslLib
{

    private $search;
    private $client;

    private $pre;
    private $back;
    private $extends;
    private $sort;
    private $id;
    private $index;
    private $size;
    private $from;
    private $graph;
    private $query;
    private $aggsX;
    private $aggsY;
    private $ids;
    private $doc;

    private $long = [];
    private $date = [];
    private $keyword = [];
    private $text = [];


    public function __construct($params, $hosts)
    {
        $this->client = ClientBuilder::create()->setHosts($hosts)->build(); //elasticsearch-php client

        $this->pre = isset($params['pre']) ? $params['pre'] : null;
        $this->back = isset($params['back']) ? $params['back'] : null;
        $this->id = isset($params['id']) ? $params['id'] : null;
        $this->index = isset($params['index']) ? $params['index'] : null;
        $this->size = isset($params['size']) ? $params['size'] : null;
        $this->from = isset($params['from']) ? $params['from'] : null;
        $this->graph = isset($params['graph']) ? $params['graph'] : null;
        $this->query = isset($params['query']) ? $params['query'] : null;
        $this->aggsX = isset($params['aggsX']) ? $params['aggsX'] : null;
        $this->aggsY = isset($params['aggsY']) ? $params['aggsY'] : null;
        $this->extends = isset($params['extends']) ? $params['extends'] : null;
        $this->sort = isset($params['sort']) ? $params['sort'] : null;
        $this->count = isset($params['extends']['countType']) ? $params['extends']['countType'] : null;

        $this->ids = isset($params['ids']) ? $params['ids'] : null;
        $this->doc = isset($params['doc']) ? $params['doc'] : null;
    }

    public function updateAll()
    {
        if (empty($this->ids)) {
            return false;
        }

        $flag = true;
        foreach ($this->ids as $index => $id) {
            $result = $this->updateOne($index, $id);
            if ($result == false) {
                $flag = false;
            }
        }

        return $flag;
    }

    public function updateOne($index, $id)
    {
        $params = [
            'index' => $index,
            'type' => 'logs',
            'id' => $id,
            'body' => [
                'doc' => $this->doc
            ]
        ];

        $response = $this->client->update($params);
        $result = isset($response['result']) ? $response['result'] : null;

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function generateSearch($size = 0, $from = 0)
    {
        $search = new Search();
        $search->setSize($size);
        $search->setFrom($from);

        return $search;
    }


    //获取所有用户数
    public function  getAllUser()
    {
        $search = $this->generateSearch(0,0);
        $alluser = "alluser";
        $aggregation = new CardinalityAggregation("$alluser","user_id");
        $search->addAggregation($aggregation);

        $queryArray = $search->toArray();

        $params = [
            'index' => $this->index,
            'body' => $queryArray,
        ];

        $result = $this->client->search($params);

        $total = isset($result['aggregations'][$alluser]['value']) ? $result['aggregations'][$alluser]['value'] : 0;

        return $total;
    }



    public function handleVersioRemain($params)
    {
        $search = $this->generateSearch($this->size, $this->from);
        $versionRemain = isset($params['versionRemain']) ? $params['versionRemain'] : "";
        $collapse = isset($params['collapse']['field']) ? $params['collapse']['field'] : "";

        $compare = $versionRemain['compare'];
        $compared = $versionRemain['compared'];
        $compareNumber = 0;
        $comparedNumber = 0;
        $compareArray = [];
        $comparedArray = [];

        $bool = new BoolQuery();
        $matchQuery = new MatchQuery('version', $compare);
        $bool->add($matchQuery, BoolQuery::MUST);
        $search->addQuery($bool);
        $search->addCollapse($collapse);
        $arguments = $this->createArea($search, [], [], $this->index);

        $compareResult = $this->client->search($arguments);
        $compareList = isset($compareResult['hits']['hits']) ? $compareResult['hits']['hits'] : [];
        if ($compareList) {
            foreach ($compareList as $compareOne) {
                $tmpValue = $compareOne['fields'][$collapse][0];
                $compareArray[] = $tmpValue;
            }
            $compareNumber = count($compareArray);
            $comparedNumber = $this->handleCompare($collapse, $compareArray, $compared);
        }

        $outputs = [];
        $temp[$compare] = $compareNumber;
        $temp[$compared] = $comparedNumber;

        $extendsArray['data'] = $temp;
        $extendsArray['extendsInfo'] = "";
        $extendsArray['label'] = "";
        $outputs['yAxis'][] = $extendsArray;

        return $outputs;
    }

    public function handleCompare($collapse, $compareArray, $compared)
    {
        $search = $this->generateSearch($this->size, $this->from);
        $bool = new BoolQuery();
        foreach ($compareArray as $compare) {
            $matchQuery = new MatchQuery($collapse, $compare);
            $bool->add($matchQuery, BoolQuery::SHOULD);
        }
        $bool->addParameter("minimum_should_match", 1);
        $versionQuery = new MatchQuery('version', $compared);
        $bool->add($versionQuery, BoolQuery::MUST);
        $search->addQuery($bool);
        $search->addCollapse($collapse);
        $arguments = $this->createArea($search, [], [], $this->index);
        $comparedResult = $this->client->search($arguments);
        if (isset($comparedResult['hits']['hits'])) {
            return count($comparedResult['hits']['hits']);
        }

    }

    public function handleNewMac($index, $firstDayTime, $secondDayTime, $collapse)
    {
        $search = $this->generateSearch($this->size, $this->from);

        $rangeQuery = new RangeQuery('created_timestring', ['gte' => $firstDayTime,'lt' => $secondDayTime]);
        $bool = new BoolQuery();
        $bool->add($rangeQuery, BoolQuery::MUST);
        $search->addQuery($bool);
        $search->addCollapse($collapse);
        $arguments = $this->createArea($search, [], [], $this->index);
        $result = $this->client->search($arguments);

        $tmpArray = isset($result['hits']['hits']) ? $result['hits']['hits'] : [];
        $macList = [];

        if ($tmpArray) {
            foreach ($tmpArray as $tmpOne) {
                $macList[] = $tmpOne['fields']['client_mac.keyword'][0];
            }
        }

        $number = $this->handlePreMac($collapse, $macList, $firstDayTime);

        return  $number;
    }

    public function handlePreMac($collapse, $macArray, $preDayTime)
    {
        $search = $this->generateSearch($this->size, $this->from);
        $bool = new BoolQuery();
        $rangeQuery = new RangeQuery('created_timestring', ['lt' => $preDayTime]);
        foreach ($macArray as $mac) {
            $matchQuery = new MatchQuery($collapse, $mac);
            $bool->add($matchQuery, BoolQuery::SHOULD);
        }
        $bool->add($rangeQuery, BoolQuery::MUST);
        $bool->addParameter("minimum_should_match", 1);
        $search->addQuery($bool);
        $search->addCollapse($collapse);
        $arguments = $this->createArea($search, [], [], $this->index);
        $result = $this->client->search($arguments);
        $macOldArray = isset($result['hits']['hits']) ? $result['hits']['hits'] : [];
        $difference = count($macArray)-count($macOldArray);
        if ($difference < 0) {
            $difference = 0;
        }

        return $difference;
    }


    public function handleActive($params, $currentTime, $timeDay, $space)
    {
        $relateDay = $this->getRelateDay($space, $offset = 0, $timeDay);
        $relateDayString = $relateDay . "T00:00:00.000Z";
        $rangeQuery = new RangeQuery('created_timestring', ['gte' => $relateDayString,'lt' => $currentTime]);
        $search = $this->generateSearch($this->size, $this->from);

        if (empty($this->index)) {
            return false;
        }

        $bool = new BoolQuery();
        $bool->add($rangeQuery, BoolQuery::MUST);
        $search->addQuery($bool);

        $aggregationsX = isset($params['aggsX']) ? $params['aggsX'] : [];
        $aggregationsY = isset($params['aggsY']) ? $params['aggsY'] : [];

        $arguments = $this->createArea($search, $aggregationsX, $aggregationsY, $this->index);

        $result = $this->client->search($arguments);

        $activeCount = isset($result['aggregations']['y1']['value']) ? $result['aggregations']['y1']['value'] : 0;

        return $activeCount;
    }

    public function createRemain($timeDay, $size, $from, $index, $space, $extendsQuery = [])
    {
        $search = $this->generateSearch($size, $from);
        $bool = new BoolQuery();
        $matchQuery1 = new MatchQuery('created_day', $timeDay);
        $matchQuery2 = new MatchQuery('event_type', 'register');
        $bool->add($matchQuery1, BoolQuery::MUST);
        $bool->add($matchQuery2, BoolQuery::MUST);
        if ($extendsQuery) {
            foreach ($extendsQuery as $query) {
                $bool->add($query, BoolQuery::MUST);
            }
        }

        $search->addQuery($bool);

        $termsAggregation = new TermsAggregation('termsfield', 'user_name');
        $search->addAggregation($termsAggregation);

        $queryArray = $search->toArray();
        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];

        $results = $this->client->search($params);
        $termsField = isset($results['aggregations']['termsfield']['buckets']) ? $results['aggregations']['termsfield']['buckets'] : [];

        $remain = [];
        $total = count($termsField);
        $validCount = 0;

        if ($termsField) {
            foreach ($termsField as $k => $v) {
                $key = $v['key'];
                $tmpResult = $this->searchRemain($key, $index, $timeDay, $space, $extendsQuery = []);
                $count = $tmpResult['total'];
                if ($count > 0) {
                    $validCount = $validCount + 1;
                }
            }
        }
        $currentDayString = $timeDay . "T00:00:00.000Z";
        $relateDay = $this->getRelateDay($space, 1,$timeDay);
        $relateDayString = $relateDay . "T00:00:00.000Z";
        $remain['total'] = $total;
        $remain['valid'] = $validCount;
        if ($total == 0) {
            $remain['percent'] = 0;
        } else {
            $remain['percent'] = $validCount/$total;
        }
        $remain['currentDayString'] = $currentDayString;
        $remain['relateDayString'] = $relateDayString;

        return $remain;
    }

    function getRelateDay($space, $offset = 0, $timeDay)
    {
        if ($offset) {
            $space = $space + $offset;
        }

        return date("Y-m-d", strtotime("$space days", strtotime($timeDay)));
    }

    function searchRemain($key, $index, $timeDay, $space, $extendsQuery = [])
    {
        $search = new Search();
        $bool = new BoolQuery();
        $relateDay = $this->getRelateDay($space, 1, $timeDay);

        $relateDayString = $relateDay . "T00:00:00.000Z";

        $matchQuery1 = new MatchQuery('user_name', $key);
        $rangeQuery = new RangeQuery('created_timestring', ['gte' => $relateDayString]);
        $bool->add($matchQuery1, BoolQuery::MUST);
        $bool->add($rangeQuery, BoolQuery::MUST);

        if ($extendsQuery) {
            foreach ($extendsQuery as $query) {
                $bool->add($query, BoolQuery::MUST);
            }
        }

        $search->addQuery($bool);
        $queryArray = $search->toArray();
        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];

        $results = $this->client->search($params);

        return $results['hits'];
    }


    public function catIndices()
    {
        $result = $this->client->cat()->indices();
        return $result;
    }

    public function mapping($index)
    {
        $params = [
            'index' => $index
        ];

        $result = $this->client->indices()->getMapping($params);
        $properties = isset($result[$index]['mappings']['logs']['properties']) ? $result[$index]['mappings']['logs']['properties'] : [];
        $this->recursionProperties($properties, "");
        $outputs = [];
        $outputs['date'] = $this->date;
        $outputs['long'] = $this->long;
        $outputs['keyword'] = $this->keyword;
        $outputs['text'] = $this->text;

        return $outputs;
    }

    public function recursionProperties($arr, $pre)
    {
        foreach ($arr as $key => $value) {
            $key = $pre.$key;
            if (isset($value['type'])) {
                if ($value['type'] == 'long') {
                    array_push($this->long, $key);
                }
                if ($value['type'] == 'text') {
                    array_push($this->text, $key);
                }
                if ($value['type'] == 'keyword') {
                    array_push($this->keyword, $key);
                }
                if ($value['type'] == 'date') {
                    array_push($this->date, $key);
                }
            }
            if (isset($value['fields']) && $value['fields']) {
                $this->recursionProperties($value['fields'], $key.".");
            }
            if (isset($value['properties']) && $value['properties']) {
                $this->recursionProperties($value['properties'], $key.".");
            }

        }
    }


    public function createQuery($search, $queryPart, $extendsQuery = [])
    {
        $bool = new BoolQuery();
        if ($queryPart) {
            foreach ($queryPart as $key => $val) {
                $className = $this->getRelation($val['func']);
                $arguments = [];
                $jsonValue = isset($val['json']) ? $val['json'] : '';
                foreach ($val as $argument_key => $argument_val) {
                    if ($argument_key == 'arguments') {
                        $arguments = $argument_val;
                    }
                }
                $tmpQuery = new $className(...$arguments);

                if ($val['type'] == "must") {
                    $bool->add($tmpQuery, BoolQuery::MUST);
                } elseif ($val['type'] == "must_not") {
                    $bool->add($tmpQuery, BoolQuery::MUST_NOT);
                }
            }
        }

        if ($extendsQuery && is_array($extendsQuery)) {
            foreach ($extendsQuery as $query) {
                $bool->add($query);
            }
        }

        return $bool;
    }


    public function createAggregations($search, $aggregationsX, $aggregationsY)
    {
        $linkMethod = null;
        $linkFunctionName = "";

        if (is_array($aggregationsX) && $aggregationsX) {
            $aggregationsX = array_reverse($aggregationsX);
            foreach ($aggregationsX as $key => $val) {
                $tmpMethod = $this->createFunc($val);

                if ($linkMethod) {
                    $methodType = $this->getType($linkFunctionName);
                    $tmpMethod->$methodType($linkMethod);
                } else {
                    if ($aggregationsY && is_array($aggregationsY)) {
                        foreach ($aggregationsY as $m => $n) {
                            if ($n['func'] != "CountAggregation") {
                                $yMethod = $this->createFunc($n);
                                $tmpMethod->addAggregation($yMethod);
                            }
                        }
                    }
                }
                $linkMethod = $tmpMethod;
                $linkFunctionName = $val['func'];
            }
            $methodType = $this->getType($linkFunctionName);
            $search->$methodType($linkMethod);
        } else {
            if ($aggregationsY && is_array($aggregationsY)) {
                foreach ($aggregationsY as $m => $n) {
                    $yMethod = $this->createFunc($n);
                    $search->addAggregation($yMethod);
                }
            }
        }
    }

    public function getTerms($split, $index)
    {
        $arguments = $split['arguments'];
        $name = $split['name'];
        $search = new Search();
        $termsAggregation = new TermsAggregation(...$arguments);
        $search->setSize(0);
        $search->addAggregation($termsAggregation);
        $queryArray = $search->toArray();
        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];
        $results = $this->client->search($params);

        $terms = [];
        $tmps = $results['aggregations'][$name]['buckets'];
        foreach ($tmps as $tmp) {
            $terms[] = $tmp['key'];
        }

        return $terms;
    }

    public function getCityList()
    {
        $chinaCityList = file_get_contents('ChinaCityList.json');
        $chinaCityListArray = json_decode($chinaCityList, 1);
        $cityLists = [];
        foreach ($chinaCityListArray as $citys) {
            $provinces = $citys['city'];
            foreach ($provinces as $countys) {
                $countys = $countys['county'];
                foreach ($countys as $county) {
                    $cityLists[] = $county;
                }
            }
        }

        return $cityLists;
    }

    function createBackAggregation($params)
    {
        $index = isset($params['index']) ? $params['index'] : null;
        $size =  isset($params['size']) ? $params['size'] : 0;
        $search = new Search();
        $search->setSize($size);

        $arguments = isset($params['back']['arguments']) ? $params['back']['arguments'] : null;
        $func = isset($params['back']['func']) ? $params['back']['func'] : null;
        $className = $this->getRelation($func);

        if (empty($arguments)) {
            return false;
        }
        $aggs = new $className(...$arguments);

        $search->addAggregation($aggs);
        $queryArray = $search->toArray();

        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];

        $result = $this->client->search($params);

        $backs = [];
        foreach ($result['aggregations']['back']['buckets'] as $key=>$value) {
            $backs[] = $value['key'];
        }

        return $backs;
    }

    function createPreAggregation($params)
    {
        $index = isset($params['index']) ? $params['index'] : null;
        $size =  isset($params['size']) ? $params['size'] : 0;
        $search = new Search();
        $search->setSize($size);

        $arguments = isset($params['pre']['arguments']) ? $params['pre']['arguments'] : null;
        $func = isset($params['pre']['func']) ? $params['pre']['func'] : null;
        $className = $this->getRelation($func);

        if (empty($arguments)) {
            return false;
        }
        $aggs = new $className(...$arguments);

        $search->addAggregation($aggs);
        $queryArray = $search->toArray();

        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];

        $result = $this->client->search($params);

        $total = isset($result['aggregations']['pre']['value']) ? $result['aggregations']['pre']['value'] : 0;

        if ($total) {
            return $total;
        } else {
            return false;
        }
    }

    function createPreQuery($params, $extendsQuerys)
    {
        $index = isset($params['index']) ? $params['index'] : null;
        $search = new Search();

        $bool = $this->createQuery($search, [], $extendsQuerys);
        $search->addQuery($bool);
        $arguments = $this->createArea($search, [], [], $index);
        $result = $this->client->search($arguments);

        return $result['hits']['total'];
    }



    public function generatePre($pre)
    {
        $extendsQuery = [];
        if (empty($pre)) {
            return $extendsQuery;
        } else {
            $extendsQuery[] = new TermQuery(...$pre['arguments']);
        }

        return $extendsQuery;
    }



    public function realResult($initParams)
    {
        $search = new Search();
        $search->setSize(0);
        $result = $this->createSearchBefore($search, $initParams, [], []);

        return $result;
    }


    public function createXY($search, $params, $splits, $index, $calculations = [])
    {
        $graph = isset($params['graph']) ? $params['graph'] : "";
        $xAxis = [];
        $outputs = [];
        $initParams = $params;
        $initParams['aggsX'] = array_slice($initParams['aggsX'], 0, 1);
        $realresult = $this->realResult($initParams);
        $totals = isset($realresult['aggregations']['x1']['buckets']) ? $realresult['aggregations']['x1']['buckets'] : [];

        if (empty($totals)) {
            $totals = isset($realresult['aggregations']['x2']['buckets']) ? $realresult['aggregations']['x2']['buckets'] : [];
        }
        if (empty($totals)) {
            return false;
        }

        $params['aggsX'] = array_reverse($params['aggsX']);
        $result = $this->createSearchBefore($search, $params, []);

        $keyName = "";
        foreach ($totals as $total) {
            if (isset($total['key_as_string'])) {
                $outputs['xAxis'][] = $total['key_as_string'];
                $keyName = "key_as_string";
            } else {
                $outputs['xAxis'][] = $total['key'];
                $keyName = "key";
            }
        }

        $yResults = [];
        $yAxis = isset($params['aggsY']) ? $params['aggsY'] : [];
        $filterFlag = $this->checkBucketSelector($yAxis);

        $outputs['yAxis'] = [];
        if ($yAxis) {
            if (empty($calculations)) {
                foreach ($yAxis as $y) {
                    if ($y['func'] == "BucketSelectorAggregation") {
                        continue;
                    }
                    $name = $y['name'];
                    $func = $y['func'];
                    $label = isset($y['label']) ? $y['label'] : '';

                    if (count($params['aggsX']) == 1) {
                        $yResult = $this->handleOneX($result, $outputs, $name, $func, $keyName, $label, $filterFlag);
                        $yResults = array_merge($yResults,$yResult);

                    } elseif (count($params['aggsX']) == 2) {
                        $yResult = $this->handleTwoX($result, $outputs, $name, $func, $keyName, $label, $filterFlag);
                        $yResults = array_merge($yResults,$yResult);
                    }
                }
            } else {
                $name1 = $calculations[0];
                $name2 = $calculations[2];
                $tmpArray = array_fill(0, count($outputs['xAxis']), 0);
                foreach ($result['aggregations']['x1']['buckets'] as $m => $n) {
                    $key = array_search($n[$keyName], $outputs['xAxis']);

                    $value1 = isset($n[$name1]['value']) ? $n[$name1]['value'] : 0;
                    $value2 = isset($n[$name2]['value']) ? $n[$name2]['value'] : $calculations[2];

                    if ($value2 == 0) {
                        $tmpArray[$key] = 0;
                    } else {
                        if ($calculations[1] == '/') {
                            $tmpArray[$key] = round($value1 / $value2, 2);
                        }
                    }
                }
                //print_r($tmpArray);exit;
                $extendsArray['data'] = $tmpArray;
                $extendsArray['extendsInfo'] = isset($result['extendsInfo']) ? $result['extendsInfo'] : "";
                $extendsArray['label'] = "";
                $yResults[] = $extendsArray;
            }
        }

        if ($graph == "map") {
            $chinaCityList = $this->getCityList();
            $realNameList = [];
            if (isset($outputs['xAxis']) && $outputs['xAxis']) {
                foreach ($outputs['xAxis'] as $k => $x) {
                    $tmp = $this->cityName($chinaCityList, $x);
                    $realNameList[] = strtoupper($tmp);
                }
            }
            $outputs['xAxis'] = $realNameList;
        }

        $outputs['yAxis'] = $yResults;

        return $outputs;
    }

    public function checkBucketSelector($yAxis)
    {
        foreach ($yAxis as $y) {
            if ($y['func'] == "BucketSelectorAggregation") {
                return true;
            }
        }

        return false;
    }

    public function handleOneX($result, $outputs, $name, $func, $keyName, $label = "", $filterFlag = false)
    {
        $yResult = [];
        $tmpArray = array_fill(0, count($outputs['xAxis']), 0);
        foreach ($result['aggregations']['x1']['buckets'] as $m => $n) {
            $key = array_search($n[$keyName], $outputs['xAxis']);

            if (isset($n[$name]['value']) && $n[$name]['value']) {
                $tmpArray[$key] = $n[$name]['value'];
            } elseif ($func == "CountAggregation") {
                $tmpArray[$key] = $n['doc_count'];
            } else {
                $tmpArray[$key] = 0;
            }
        }

        $extendsArray['data'] = $tmpArray;
        $extendsArray['extendsInfo'] = isset($result['extendsInfo']) ? $result['extendsInfo'] : "";
        $extendsArray['label'] = $label;

        $sum = array_sum($tmpArray);
        if ($filterFlag) {
            if($sum) {
                $yResult[] = $extendsArray;
            }
        } else {
            $yResult[] = $extendsArray;
        }

        return $yResult;
    }

    public function handleTwoX($result, $outputs, $name ,$func, $keyName = "", $label = "", $filterFlag = false)
    {
        $yResult = [];

        if ($this->count) {

            $tmpArray = array_fill(0, count($outputs['xAxis']), 0);
            $x2Terms = $result['aggregations']['x2']['buckets'];

            if ($x2Terms && is_array($x2Terms)) {
                $splitInfo = "";
                foreach ($x2Terms as $x2Term) {
                    $splitInfo = $x2Term['key'];
                    $x1Terms = isset($x2Term['x1']['buckets']) ? $x2Term['x1']['buckets'] : null;
                    if ($x1Terms) {
                        foreach ($x1Terms as $x1Term) {
                            $x1TermTmp = isset($x1Term['key_as_string']) ? $x1Term['key_as_string'] : null;
                            if (empty($x1TermTmp)) {
                                $x1TermTmp = isset($x1Term['key']) ? $x1Term['key'] : null;
                            }

                            $key = array_search($x1TermTmp, $outputs['xAxis']);

                            if (isset($x1Term[$name]['value']) && $x1Term[$name]['value']) {
                                if ($x1Term[$name]['value'] > 0) {
                                        $tmpArray[$key] += 1;
                                }
                            } elseif ($func == "CountAggregation") {
                                $tmpArray[$key] = $x1Term['doc_count'];
                            } else {
                                $tmpArray[$key] = 0;
                            }
                        }
                    }
                }

                $extendsArray['data'] = $tmpArray;
                $extendsArray['extendsInfo'] = "";
                $extendsArray['label'] = $label;

                $sum = array_sum($tmpArray);
                if ($filterFlag) {
                    if($sum) {
                        $yResult[] = $extendsArray;
                    }
                } else {
                    $yResult[] = $extendsArray;
                }
            }
        }else {
            $x2Terms = $result['aggregations']['x2']['buckets'];
            if ($x2Terms && is_array($x2Terms)) {
                $splitInfo = "";
                foreach ($x2Terms as $x2Term) {
                    if (isset($x2Term['key_as_string'])) {
                        $splitInfo =   $x2Term['key_as_string'];
                    } else {
                        $splitInfo =   $x2Term['key'];
                    }

                    $tmpArray = array_fill(0, count($outputs['xAxis']), 0);
                    $x1Terms = isset($x2Term['x1']['buckets']) ? $x2Term['x1']['buckets'] : null;

                    if ($x1Terms) {
                        foreach ($x1Terms as $x1Term) {
                            $x1TermTmp = isset($x1Term['key_as_string']) ? $x1Term['key_as_string'] : null;
                            if (empty($x1TermTmp)) {
                                $x1TermTmp = isset($x1Term['key']) ? $x1Term['key'] : null;
                            }

                            $key = array_search($x1TermTmp, $outputs['xAxis']);

                            if (isset($x1Term[$name]['value']) && $x1Term[$name]['value']) {
                                $tmpArray[$key] = $x1Term[$name]['value'];
                            } elseif ($func == "CountAggregation") {
                                $tmpArray[$key] = $x1Term['doc_count'];
                            } else {
                                $tmpArray[$key] = 0;
                            }
                        }
                    }

                    $extendsArray['data'] = $tmpArray;
                    $extendsArray['extendsInfo'] = $splitInfo;
                    $extendsArray['label'] = $label;

                    //判断数组是否为全空数组
                    $sum = array_sum($tmpArray);
                    if ($filterFlag) {
                        if($sum) {
                            $yResult[] = $extendsArray;
                        }
                    } else {
                        $yResult[] = $extendsArray;
                    }
                }

            }
        }

        return $yResult;
    }


    public function createMap($params, $splits, $index)
    {
        $outputs = [];
        if ($splits && is_array($splits)) {
            $extends = $this->generateSplits($index, $splits);
            foreach ($extends as $extend) {
                $tmpArray = [];
                $tmpArray['extendsInfo'] = $extend['extendsInfo'];
                $tmpArray['cityName'] = isset($extend['cityName']) ? $extend['cityName']: "";
                $search = new Search();
                $result = $this->createSearchBefore($search, $params, $extend['query'], $extend['extendsInfo']);
                $tmpArray['data'] = isset($result['hits']['total']) ? $result['hits']['total'] : 0;

                $outputs['yAxis'][] = $tmpArray;

            }
        } else {
            return false;
        }

        return $outputs;
    }

    public function createY($search, $params)
    {
        $result = $this->createSearchBefore($search, $params, []);

        $yAxis = isset($params['aggsY']) ? $params['aggsY'] : [];
        $outputs['yAxis'] = [];

        if ($yAxis) {
            foreach ($yAxis as $y) {
                $name = $y['name'];
                $tmpArray = [];
                if (isset($result['aggregations'])) {
                    $tmpArray['data'][] = $result['aggregations'][$name]['value'];
                    $tmpArray['extendsInfo'] = isset($result['extendsInfo']) ? $result['extendsInfo'] : "";
                    $tmpArray['label'] = isset($y['label']) ? $y['label'] : '';
                }
                $outputs['yAxis'][] = $tmpArray;
            }
        } else {
            if(isset($result['hits'])) {
                $tmpArray= [];
                $tmpArray['extendsInfo'] = isset($result['extendsInfo']) ? $result['extendsInfo'] : "";
                $tmpArray['data'][] = $result['hits']['total'];
                $outputs['yAxis'][] = $tmpArray;
            }
        }

        return $outputs;
    }

    public function createTable($search, $params)
    {
        $results = $this->createSearchBefore($search, $params, []);
        $x1Terms = $results['aggregations']['x1']['buckets'];

        $outputs['xAxis'] = [];
        $outputs['yAxis'] = [];

        $yAxis = isset($params['aggsY']) ? $params['aggsY'] : [];
        if ($yAxis) {
            foreach ($yAxis as $y) {
                $name = $y['name'];
                $label = $y['label'];
                $tmpArray = [];
                foreach ($x1Terms as $x1Term) {
                    $outputs['xAxis'][] = isset($x1Term['key_as_string']) ? $x1Term['key_as_string'] : "";
                    $x2Terms = $x1Term['x2']['buckets'];
                    $tmpValue = [];
                    foreach ($x2Terms as $x2Term) {
                        $key = $x2Term['key'];
                        if (isset($x2Term[$name]['value'])) {
                            $miniArray = [];
                            $miniArray[$key] = $x2Term[$name]['value'];
                            $tmpValue[] = $miniArray;
                        } else {
                            $miniArray = [];
                            $miniArray[$key] = isset($x2Term['doc_count']) ? $x2Term['doc_count'] : 0;
                            $tmpValue[] = $miniArray;
                        }
                    }

                    $tmpArray['data'] = $tmpValue;
                    $tmpArray['extendsInfo'] = "";
                    $tmpArray['label'] = $label;

                    $outputs['yAxis'][] = $tmpArray;
                }
            }
        }
        $outputs['xAxis'] = array_reverse($outputs['xAxis']);
        $outputs['yAxis'] = array_reverse($outputs['yAxis']);

        return $outputs;
    }

    public function anchorList($search, $params)
    {
        $index = isset($params['index']) ? $params['index'] : "";
        $generate = isset($params['generate']) ? $params['generate'] : null;

        $outputs = $this->createSearchBefore($search, $params, []);
        $total = isset($outputs['hits']['total']) ? $outputs['hits']['total'] : 0;
        $list['data'] = isset($outputs['hits']['hits']) ? $outputs['hits']['hits'] : [];
        $list['count'] = $total;

        if ($generate == "excel") {
            $firstRaw = ["主播名","平台昵称","开播地址","开播时间","结束时间","持续时间"];
            $tableData = [];
            $tableData[] =  $firstRaw;
            foreach ($list['data'] as $raw) {
                $tmpArray = [];
                $user_name = isset($raw['_source']['user_name']) ? $raw['_source']['user_name'] : "";

                $nickname = "";
                $liveurl = "";
                $nicknameArray = [];
                $liveurlArray = [];
                $live_info = isset($raw['_source']['event_data']['liveinfo']) ? $raw['_source']['event_data']['liveinfo'] : [];
                foreach ($live_info as $info) {
                    $nicknameArray[] = $info['nickname'];
                    $liveurlArray[] = $info['liveurl'];
                }
                if ($nicknameArray && is_array($nicknameArray)) {
                    $nickname = implode(",", $nicknameArray);
                }
                if ($liveurlArray && is_array($liveurlArray)) {
                    $liveurl = implode(",", $liveurlArray);
                }

                $created_timestamp = isset($raw['_source']['created_timestamp']) ? $raw['_source']['created_timestamp'] : 0;
                $stopStreaming = date("Y-m-d H:i:s",$created_timestamp/1000);
                $event_time = isset($raw['_source']['event_data']['event_time']) ? $raw['_source']['event_data']['event_time'] : 0;
                $startStreaming = date("Y-m-d H:i:s",($created_timestamp/1000 - $event_time));
                $continueStreaming = $this->changeTimeType($event_time);

                $tmpArray[] = $user_name;
                $tmpArray[] = $nickname;
                $tmpArray[] = $liveurl;
                $tmpArray[] = $startStreaming;
                $tmpArray[] = $stopStreaming;
                $tmpArray[] = $continueStreaming;

                $tableData[] = $tmpArray;
            }

            $excel['tableData'] = $tableData;
            $excel['tableName'] = "主播开播查询";
            $id = $this->handleExcel($excel);
            $excelinfo['export_id'] = $id;

            return $excelinfo;
        }

        return $list;
    }

    public function createOnline($search, $params)
    {
        $results = $this->createSearchBefore($search, $params, []);
        $sorts = isset($params['sort']) ? $params['sort'] : [];
        $onlinesize = isset($params['size']) ? $params['size'] : 0;
        $onlinefrom = isset($params['from']) ? $params['from'] : 0;
        $generate = isset($params['generate']) ? $params['generate'] : null;

        $onlineInfos = [];
        $users = [];
        //分析结果
        $x1Terms = $results['aggregations']['x1']['buckets'];
        foreach ($x1Terms as $x1Term) {
            $doc_count = $x1Term['doc_count'];
            $key =$x1Term['key'];
            if ($doc_count == 1 && $key) {
                $extendsQuery = [];
                $extendsQuery[] = new TermQuery("event_data.streaming_id", $key);
                $tmpSearch = $this->generateSearch(10, 0);
                $initParams = $params;
                $initParams['size'] = 10;
                $initParams['from'] = 0;
                unset($initParams['aggsX']);
                unset($initParams['aggsY']);
                $tmpResult = $this->createSearchBefore($tmpSearch, $initParams, $extendsQuery);
                $total = isset($tmpResult['hits']['total']) ? $tmpResult['hits']['total'] : 0;
                if ($total == 1) {
                    $event_type = isset($tmpResult['hits']['hits'][0]['_source']['event_type']) ? $tmpResult['hits']['hits'][0]['_source']['event_type'] : "";
                    $user_name = isset($tmpResult['hits']['hits'][0]['_source']['user_name']) ? $tmpResult['hits']['hits'][0]['_source']['user_name'] : "";
                    if ($event_type == "startStreaming" && (!in_array($user_name, $users))) {
                        $onlineInfos[] = isset($tmpResult['hits']['hits'][0]) ? $tmpResult['hits']['hits'][0] : [];
                        $users[] = $user_name;
                    }
                }
            }
        }

        $list['data'] = array_slice($onlineInfos, $onlinefrom, $onlinesize);
        $list['count'] = count($onlineInfos);

        if ($generate == "excel") {
            $firstRaw = ["主播名", "平台昵称", "开播地址", "开播时间", "结束时间", "持续时间"];
            $tableData = [];
            $tableData[] = $firstRaw;
            foreach ($list['data'] as $raw) {
                $tmpArray = [];
                $user_name = isset($raw['_source']['user_name']) ? $raw['_source']['user_name'] : "";

                $nickname = "";
                $liveurl = "";
                $nicknameArray = [];
                $liveurlArray = [];
                $live_info = isset($raw['_source']['event_data']['liveinfo']) ? $raw['_source']['event_data']['liveinfo'] : [];
                foreach ($live_info as $info) {
                    $nicknameArray[] = $info['nickname'];
                    $liveurlArray[] = $info['liveurl'];
                }
                if ($nicknameArray && is_array($nicknameArray)) {
                    $nickname = implode(",", $nicknameArray);
                }
                if ($liveurlArray && is_array($liveurlArray)) {
                    $liveurl = implode(",", $liveurlArray);
                }

                $created_timestamp = isset($raw['_source']['created_timestamp']) ? $raw['_source']['created_timestamp'] : 0;
                $startStreaming = date("Y-m-d H:i:s", $created_timestamp / 1000);
                $event_time = isset($raw['_source']['event_data']['event_time']) ? $raw['_source']['event_data']['event_time'] : 0;
                $event_time = time() - round($created_timestamp / 1000);
                $stopStreaming = "直播中";
                $continueStreaming = $this->changeTimeType($event_time);

                $tmpArray[] = $user_name;
                $tmpArray[] = $nickname;
                $tmpArray[] = $liveurl;
                $tmpArray[] = $startStreaming;
                $tmpArray[] = $stopStreaming;
                $tmpArray[] = $continueStreaming;

                $tableData[] = $tmpArray;
            }
            $excel['tableData'] = $tableData;
            $excel['tableName'] = "主播在线查询";
            $excelinfo['export_id'] = $this->handleExcel($excel);

            return $excelinfo;
        }

        return $list;
    }

    public function createSearch($params)
    {
        $outputs = "";
        $index = isset($params['index']) ? $params['index'] : '';

        if (empty($index)) {
            return $this->error("index索引为空");
        }

        $graph = isset($params['graph']) ? $params['graph'] : '';
        if (empty($graph)) {
            return $this->error("graph不能为空");
        }

        $splits = isset($params['splits']) ? $params['splits'] : '';

        $calculations = isset($params['extends']['calculations']) ? $params['extends']['calculations'] : [];

        $search = new Search();

        switch ($graph) {
            case "online":
                $outputs = $this->createOnline($search, $params);
                break;
            case "anchorList":
                $outputs = $this->anchorList($search, $params);
                break;
            case "table":
                $outputs = $this->createTable($search, $params, []);
                break;
            case "list" :
                $outputs = $this->createList($search, $params);
                break;
            case "map" :
            case "pie" :
            case "area":
            case "remain":
            case "active":
            case "line":
            case "horizontalBar":
            case "verticalBar":
            case "newMac":
                $outputs = $this->createXY($search, $params, $splits, $index, $calculations);
                break;
            case "metric":
                $outputs = $this->createY($search, $params);
                break;
            default:
                $this->error("未知的graph类型");
                break;
        }

        return $outputs;
    }


    public function createList($search, $params){
        $index = isset($params['index']) ? $params['index'] : "";
        $outputs = $this->createSearchBefore($search, $params, []);
        $total = isset($outputs['hits']['total']) ? $outputs['hits']['total'] : 0;
        $list['data'] = isset($outputs['hits']['hits']) ? $outputs['hits']['hits'] : [];
        $list['count'] = $total;

        return $list;
    }

    public function createSearchBefore($search, $params, $extendsQuery, $extendsInfo = "")
    {
        $index = isset($params['index']) ? $params['index'] : '';
        if (empty($index)) {
            return $this->error("index索引为空");
        }
        $graph = isset($params['graph']) ? $params['graph'] : '';
        if (empty($graph)) {
            return $this->error("graph不能为空");
        }
        $size = isset($params['size']) ? $params['size'] : 0;
        $sorts = isset($params['sort']) ? $params['sort'] : [];
        $from = isset($params['from']) ? $params['from'] : 0;

        $queryPart = isset($params['query']) ? $params['query'] : '';

        $search->setSize($size);
        $search->setFrom($from);

        if ($sorts) {
            foreach ($sorts as $sort) {
                $sortObject = new FieldSort(...$sort);
                $search->addSort($sortObject);
            }
        }

        if ($queryPart) {
        $bool = $this->createQuery($search, $queryPart, $extendsQuery);
        if ($bool) {
            $search->addQuery($bool);
        }
        } else {
            return $this->error("query为空");
        }

        $aggregationsX = isset($params['aggsX']) ? $params['aggsX'] : [];
        $aggregationsY = isset($params['aggsY']) ? $params['aggsY'] : [];

        $arguments = $this->createArea($search, $aggregationsX, $aggregationsY, $index);

        $result = $this->client->search($arguments);

        return $result;
    }

    public function createMetric($search, $aggregationsX, $aggregationsY = [], $index)
    {
        if ($aggregationsX) {
            $this->createAggregations($search, $aggregationsX, $aggregationsY);
        } else {
            return $this->error("Aggregation为空");
        }

        $queryArray = $search->toArray();
        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];

        return $params;
    }

    public function createArea($search, $aggregationsX = [], $aggregationsY = [], $index)
    {

        $this->createAggregations($search, $aggregationsX, $aggregationsY);
        $queryArray = $search->toArray();

        $params = [
            'index' => $index,
            'body' => $queryArray,
        ];

        return $params;
    }


    public function createFunc($funcInfo)
    {
        $object = null;
        $arguments = [];

        if (is_array($funcInfo)) {
            foreach ($funcInfo as $key => $val) {
                if ($key == "func") {
                    $val = trim($val);
                    $className = $this->getRelation($val);
                } elseif ($key == "arguments") {
                    $arguments = $val;
                }
            }
        }

        $object = new $className(...$arguments);

        $condition = isset($funcInfo['condition']) ? $funcInfo['condition'] : '';

        if ($className == 'ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketSelectorAggregation') {
            $object->setScript($condition);
        }

        return $object;
    }


    public function getRelation($key)
    {
        $relations = $this->relations();
        $functioniName = isset($relations[$key]) ? $relations[$key] : '';
        if ($functioniName) {
            return $functioniName;
        }
    }

    public function relations()
    {
        return [
            'DateHistogramAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation',
            'DateRangeAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateRangeAggregation',
            'HistogramAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\HistogramAggregation',
            'FiltersAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\FiltersAggregation',
            'TermsAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation',
            'SignificantTermsAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\SignificantTermsAggregation',
            'RangeAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Bucketing\RangeAggregation',
            'SumAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation',
            'AvgAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\AvgAggregation',
            'CardinalityAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\CardinalityAggregation',
            'MaxAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\MaxAggregation',
            'MinAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\MinAggregation',
            'PercentilesAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\PercentilesAggregation',
            'TopHitsAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation',
            'BucketSelectorAggregation' => 'ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketSelectorAggregation',
            'MatchAllQuery' => 'ONGR\ElasticsearchDSL\Query\MatchAllQuery',
            'MatchQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\MatchQuery',
            'RangeQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery',
            'IndicesQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\IndicesQuery',
            'TermQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery',
            'MatchPhraseQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery'

        ];
    }


    public function getType($type)
    {
        if (strstr($type, "Aggregation")) {
            return "addAggregation";
        }
        if (strstr($type, "Query")) {
            return "addQuery";
        }
    }

    public function Error($error, $extends = [])
    {
        $errors = [];
        $errors['code'] = -1;
        $errors['message'] = $error;
        array_merge($errors, $extends);

        return json_encode($errors);
    }

    public function Success($success, $extends = [])
    {
        $successes = [];
        $successes['code'] = 0;
        $successes['message'] = $success;
        $successes = array_merge($successes, $extends);

        return json_encode($successes);
    }

    public function cityName($cityLists, $pinyin)
    {
        $pinyin = strtolower($pinyin);
        foreach ($cityLists as $city) {
            $cityName = strtolower($city['countyPY']);
            if ($pinyin == $cityName) {
                return trim($city['countyName']);
            }
        }

        return $pinyin;
    }

    public function getCount($index)
    {
        $params = [
            'index' => $index
        ];

        return $this->client->count($params);
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
            $extends['id'] = $result;
            return $result;
        } else {
            return $this->Error("tableData保存失败");
        }
    }

    public function changeTimeType($seconds){
        if ($seconds > 3600){
            $hours = intval($seconds/3600);
            $minutes = $seconds % 3600;
            $time = $hours.":".gmstrftime('%M:%S', $minutes);
        }else{
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }

    /*
    public function createPie($params, $splits, $index)
    {
        if ($splits && is_array($splits)) {
            $extends = $this->generateSplits($index, $splits);
            foreach ($extends as $extend) {
                $tmpArray = [];
                $tmpArray['extendsInfo'] = $extend['extendsInfo'];
                $search = new Search();
                $result = $this->createSearchBefore($search, $params, $extend['query'], $extend['extendsInfo']);

                $tmpArray['data'] = isset($result['hits']['total']) ? $result['hits']['total'] : 0;

                $outputs['yAxis'][] = $tmpArray;

            }
        } else {
            return false;
        }

        return $outputs;
    }

    public function generateSplits($index, $splits)
    {
        $cityLists = $this->getCityList();

        $extendsQuerys = [];

        if (empty($splits)) {
            return $extendsQuerys;
        }
        if (count($splits) == 1) {
            $terms1 = $this->getTerms($splits[0], $index);
            for ($i = 0; $i < count($terms1); $i++) {
                $extendsQuery = [];
                $extendsQuery['query'][] = new TermQuery($splits[0]['arguments'][1], $terms1[$i]);
                $extendsInfo = $splits[0]['arguments'][1] . " : " . $terms1[$i];
                if ($splits[0]['arguments'][1] == 'geoip.region_name' || $splits[0]['arguments'][1] == 'geoip.city_name') {
                    $cityName = $this->cityName($cityLists,$terms1[$i]);
                    $extendsQuery['cityName'] = $cityName;
                    $extendsInfo = $splits[0]['arguments'][1] . " : " . $cityName;
                }
                $extendsQuery['extendsInfo'] = $extendsInfo;
                $extendsQuerys[] = $extendsQuery;
            }
        } elseif (count($splits) == 2) {
            $terms1 = $this->getTerms($splits[0], $index);
            $terms2 = $this->getTerms($splits[1], $index);
            for ($i = 0; $i < count($terms1); $i++) {
                for ($j = 0; $j < count($terms2); $j++) {
                    $extendsQuery = [];
                    $extendsQuery['query'][] = new TermQuery($splits[0]['arguments'][1], $terms1[$i]);
                    $extendsQuery['query'][] = new TermQuery($splits[1]['arguments'][1], $terms2[$j]);
                    $extendsInfo = $splits[0]['arguments'][1] . " : " . $terms1[$i] . " " . $splits[1]['arguments'][1] . " : " . $terms2[$j];
                    if ($splits[0]['arguments'][1] == 'geoip.region_name' || $splits[0]['arguments'][1] == 'geoip.city_name') {
                        $cityName = $this->cityName($cityLists,$terms1[$i]);
                        $extendsQuery['cityName'] = $cityName;
                        $extendsInfo = $splits[0]['arguments'][1] . " : " . $cityName . " " . $splits[1]['arguments'][1] . " : " . $terms2[$j];
                    }

                    if ($splits[1]['arguments'][1] == 'geoip.region_name' || $splits[1]['arguments'][1] == 'geoip.city_name') {
                        $cityName = $this->cityName($cityLists,$terms2[$j]);
                        $extendsQuery['cityName'] = $cityName;
                        $extendsInfo = $splits[0]['arguments'][1] . " : " . $terms1[$i] . " " . $splits[1]['arguments'][1] . " : " . $cityName;
                    }

                    $extendsQuery['extendsInfo'] = $extendsInfo;
                    $extendsQuerys[] = $extendsQuery;
                }
            }
        }

        return  $extendsQuerys;
    }

    public function createSplits($params, $index, $splits)
    {
        if (count($splits) == 1) {
            $tmpArrays = [];
            $terms1 = $this->getTerms($splits[0], $index);

            $arguments1 = $splits[0]['arguments'];
            $results = [];
            for ($i = 0; $i < count($terms1); $i++) {
                $search = $this->generateSearch($this->size, $this->from);
                $extendsQuery = [];
                $extendsQuery[] = new TermQuery($splits[0]['arguments'][1], $terms1[$i]);
                $extendsInfo = $splits[0]['arguments'][1] . " : " . $terms1[$i];

                $result = $this->createSearchBefore($search, $params, $extendsQuery, $extendsInfo);

                $results[$splits[0]['arguments'][1] . " : " . $terms1[$i]] = $result;
            }
        }

        if (count($splits) == 2) {
            $tmpArrays = [];
            $terms1 = $this->getTerms($splits[0], $index);
            $terms2 = $this->getTerms($splits[1], $index);

            $arguments1 = $splits[0]['arguments'];
            $arguments2 = $splits[1]['arguments'];
            $results = [];
            for ($i = 0; $i < count($terms1); $i++) {
                for ($j = 0; $j < count($terms2); $j++) {
                    $search = $this->generateSearch($this->size, $this->from);
                    $extendsQuery = [];
                    $extendsQuery[] = new TermQuery($splits[0]['arguments'][1], $terms1[$i]);
                    $extendsQuery[] = new TermQuery($splits[1]['arguments'][1], $terms2[$j]);

                    $extendsInfo = $splits[0]['arguments'][1] . " : " . $terms1[$i] . " " . $splits[1]['arguments'][1] . " : " . $terms2[$j];
                    $result = $this->createSearchBefore($search, $params, $extendsQuery, $extendsInfo);
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    */
}
