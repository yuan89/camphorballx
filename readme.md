樟脑丸 八爪鱼后端API

# 用户相关

### 注册
* URL  :    http://camphorball.dev/user/register
* 类型  POST
* 字段:
* username  	string类型
* password	        string类型
* email		        string类型
* 范例
```
{
    "username":"八爪鱼",
    "password": "123456",
   "email":123456@qq.com
}
```

### 登录 
* URL  :    httphttp://camphorball.dev/user/login
* 类型  POST
* username 	string类型
* password	        string类型
* 范例
```
{
    "username":"八爪鱼",
    "password":"123456"
}
```

### 获取用户信息 
* URL  :   http://camphorball.dev/user/info
* 类型 GET

* 样例
http://camphorball.dev/user/info?api_token=vbFFKX7TDs1Bk4NVGRD787a4KoWfwDojuWy2KNqlIzeNwtXSTAlpbDXbTllG


# 用户权限

###  新增角色
* URL  :    http://camphorball.dev/rbac/role/create
* 类型  POST
*  name  string
*  description string
* 范例
```
     {
	"name":"新角色",
	"description":"新角色来管理的"
     }
```

### 新增权限
* URL  :  http://camphorball.dev/rbac/permission/create
* type:  POST
* name string
* description string
*  范例
```
   {
	"name":"新权限",
	"description":"新权限来管理的"
     }
```

### 归属角色
* URL  :  http://camphorball.dev/rbac/user/attachRole
* type :  POST
* username String
* roles  Array
* 范例
```
   {
	"username":"八爪鱼",
	"roles":["编辑","总编"]
     }
```

### 归属权限
* URL  : http://camphorball.dev/rbac/role/attachPermission
* type : POST
* role  String
* permissions Array
* 范例

```
   {
	"role":"编辑",
	"permissions ":["create","edit"]
     }
```

### 角色列表
* URL  : http://camphorball.dev/rbac/roles/list
* type : GET

### 权限列表
* URL  : http://camphorball.dev/rbac/permissions/list
* type : GET

### 用户角色列表
* URL  :  http://camphorball.dev/rbac/user/roles
* type : GET
* 范例
```
{
    "username":"asd"
}
```

### 用户权限列表
* URL  :  http://camphorball.dev/rbac/user/permissions
* type : POST
* 范例 : 

```
{
    "username":"xjk"
}
```

### 角色权限列表
* URL  :  http://camphorball.dev/rbac/role/permissions
* type : POST
* 范例
```
{
    "role":"owner"
}
```


### 用户是否具有权限
* URL :  http://camphorball.dev/rbac/user/hasPermission
* type : POST
* username String
* permission String
* 范例
```
{
	"username":"michele",
	"permission":"create-post"
}
```

###  用户列表
* URL  :  http://camphorball.dev/rbac/user/list
* type : GET

### 删除用户
* URL  :  http://camphorball.dev/rbac/user/delete
* type : POST
* 范例
```
{
     "id":"8"
}
```

### 更新角色
* URL : http://camphorball.dev/rbac/role/update/{id}
* type : POST
* 范例
```
     {
	"name":"更新角色",
	"description":"更新角色来管理的"
     }
```

### 删除角色
* URL : http://camphorball.dev/rbac/role/delete
* type : POST
* 范例
```
{
    "id":"3"
}
```

### 更新权限
* URL : http://camphorball.dev/rbac/permission/update/{id}
* type : POST
* 范例:
```
     {
	"name":"更新权限",
	"description":"更新权限来管理的"
     }
```

### 删除权限
* URL : http://camphorball.dev/rbac/permission/delete
* type : POST
* 范例
```
{
    "id":"2"
}
```

### 用户拥有产品列表

* URL : http://camphorball.dev/user/getproduct
* type : POST
* 范例
```
{
    "username":"admin"
}
```

### 用户关联产品
* URL : http://camphorball.dev/user/userProduct
* type : POST
* 范例
```
{
"user_id":1,
"product_ids":"1,3"
}
```

# panel接口

### 新增visualize
* URL : http://camphorball.dev/panel/visualize/create
* type : POST

* 范例
```
{
	"username":"dashen",
	"role":"owner",
	"name":"月下载量",
	"description":"每个月的下载量统计分析",
	"type":"area",
	"template":{
	    "index":"bzs-*",
           "graph":"area",
	   "query":[
                 {"type":"must","func":"MatchQuery","arguments":["channel_name","渠道1"]},
                 {"type":"must","func":"RangeQuery","arguments":["created_timestamp",[{"from":"1499737836959","to":"1507542811000"}] ]}
          ],
	    "size":0,
	    "aggs":[{
	        "func":"DateHistogramAggregation",
	        "name": "x1",
	        "arguments":["x1", "created_timestamp","1d"]
	    },
	    {
	        "func":"TermsAggregation",
	        "name":"x2",
	        "arguments":[ "x2", "user_name"]
	    },
	    {
	        "func":"AvgAggregation",
	        "name":"y1",
	        "arguments":[ "y1","event_data.event_time"]
	    },
	    {
	        "func":"SumAggregation",
	        "name":"y2",
	        "arguments": ["y2","event_data.event_time"]
	    }
	    ]
	
	}
}
```


### 更新visualize
* URL : http://camphorball.dev/panel/visualize/update
* type : POST
* 范例:
```
{
       "id":1,
	"name":"月下载量",
	"description":"每个月的下载量统计分析",
	"type":"area",
	"template":{
	    "index":"bzs-*",
           "graph":"area",
	   "query":[
                 {"type":"must","func":"MatchQuery","arguments":["channel_name","渠道1"]},
                 {"type":"must","func":"RangeQuery","arguments":["created_timestamp",[{"from":"1499737836959","to":"1507542811000"}] ]}
          ],
	    "size":0,
	    "aggs":[{
	        "func":"DateHistogramAggregation",
	        "name": "x1",
	        "arguments":["x1", "created_timestamp","1d"]
	    },
	    {
	        "func":"TermsAggregation",
	        "name":"x2",
	        "arguments":[ "x2", "user_name"]
	    },
	    {
	        "func":"AvgAggregation",
	        "name":"y1",
	        "arguments":[ "y1","event_data.event_time"]
	    },
	    {
	        "func":"SumAggregation",
	        "name":"y2",
	        "arguments": ["y2","event_data.event_time"]
	    }
	    ]
	
	}
}
```

### 删除visualize
* URL : http://camphorball.dev/panel/visualize/delete
* type : POST
* 范例
```
{
    "id":"1"
}
```

### Visualize根据产品名获取
* URL: http://camphorball.dev/panel/visualize/getbyproduct
* type : POST
* 范例
```
{
     "product_id":8
}


### 新增Dashboard
* URL : http://camphorball.dev/panel/dashboard/create
* type : POST

* 范例
```
{
	"username":"dashen",
	"name":"月季度营销情况",
	"description":"市场每月营销情况报告",
	"visualize_ids":"1,2",
        "product_id":"8"
}
```

### 更新Dashboard
* URL : http://camphorball.dev/panel/dashboard/update
* type : POST

* 范例
```
{
        "id":1,
	"name":"月季度营销情况",
	"description":"市场每月营销情况报告",
	"visualize_ids":"1,2",
        "product_id":"8,9,10"
}
```


### 删除Dashboard
* URL : http://camphorball.dev/panel/dashboard/delete
* type : POST
* 范例
```
{
    "id":"1"
}
```


### Dashboard 根据产品名获取
* URL: http://camphorball.dev/panel/dashboard/getbyproduct
* type : POST
* 范例
```
{
     "product_id":8
}
```

### visualize列表
* URL : http://camphorball.dev/panel/visualize/list
* type: POST
* 范例

```
{
    "dashboard_id":"29"
}
```

### dashboard列表
* URL : http://camphorball.dev/panel/dashboard/list
* type : POST
* 范例

```
{
    "username" : "asd"
}
```

###   获取单个dashboard
* URL : http://camphorball.dev/panel/dashboard/{id}
* type: GET

### indices 新建
* URL : http://camphorball.dev/panel/indices/create
* type : POST
* 范例
```
{
    "name":"bzs-*",
    "description": "播助手索引"，
    "role_id":"1,2",
    "product_id":8
}
```

### indices 更新
* URL : http://camphorball.dev/panel/indices/update
* type : POST
* 范例
```
{
    "id":1,
    "name":"bzs-*",
    "description": "播助手索引"，
    "role_id":"1,2",
    "product_id":8
}
```

### indices 根据产品名获取
* URL: http://camphorball.dev/panel/indices/getbyproduct
* type : POST
* 范例
```
{
     "product_id":8
}
```

### indices 角色分配索引
* URL : http://camphorball.dev/panel/indices/rupdate
* type : POST
* 范例
```
{
    "role_id" : 1,
     "indice_ids":" 3,19"
}
```

### indices 角色的列表
* URL : http://camphorball.dev/panel/indices/list
* type: POST
* 范例
```
{
    "username":"admin"
}
```

### indices 所有列表
* URL : http://camphorball.dev/panel/indices/totallist
* type: GET

### product新增
* URL: http://camphorball.dev/panel/product/create
* type:POST
* 范例:
```
{
    "name":"产品1",
    "description":"产品描述",
    "role_id":"1,2",
    "parent":0
}
```

### product更新
* URL: http://camphorball.dev/panel/product/update
* type:POST
* 范例:
```
{
    "id":1,
    "name":"产品1",
    "description":"产品描述",
    "role_id":"1,2",
    "parent":3
}
```

### product删除
* URL: http://camphorball.dev/panel/product/delete
* type:POST
* 范例:
```
{
    "id":1
}
```

### product所有列表
* URL:http://camphorball.dev/panel/product/totallist
* type:GET

### product所属角色
* URL: http://camphorball.dev/panel/product/productRole
* type:POST
* 范例:
```
{
	"id":3,
	"role_id":"123"
}
```

### product关联索引
* URL : http://camphorball.dev/panel/product/productIndex
* type : POST
* 范例 : 
```
{
    "product_id":8
    "indices_ids":"3,19"
}
```

### product关联组件
* URL : http://camphorball.dev/panel/product/productVisualize
* type : POST
* 范例 : 
```
{
    "product_id":8
    "visualize_ids":"64,68"
}
```

### product关联报表
* URL : http://camphorball.dev/panel/product/productDashboard
* type : POST
* 范例 : 
```
{
    "product_id":8
    "dashboard_ids":"26,27"
}
```

### product关联用户
* URL : http://camphorball.dev/panel/product/productUser
* type : POST
* 范例 : 
```
{
    "product_id":8
    "user_ids":"1,10"
}
```

# elasticsearch请求相关接口

### 数据更新
* URL: http://camphorball.dev/elasticsearch/update
* type: POST
* 范例
```
{
	"ids":{"bzsh-2017.10.13":"AV8U3ABHcUXn-lF_c_AE"},
	"doc":{"user_name":"13018075193_6","user_id":560}
}
```

### metric接口

*  URL  : http://camphorball.dev/elasticsearch/create
*  范例

```
{
    "index":"bzs*",
    "graph":"metric",
    "query":[
             {"type":"must","func":"MatchQuery","arguments":["channel_name","渠道1"]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestamp",[{"from":"1499737836959","to":"1507542811000"}] ]}
        ],
    "size":0,
    "aggsY":[
    {
        "func":"SumAggregation",
        "name":"y1",
        "arguments":["y1", "event_data.event_time"]
    },
    {
        "func":"CardinalityAggregation",
        "name":"y2",
        "arguments": ["y2","user_id"]
    }
    ]

}

{"type":"must","func":"MatchAllQuery","arguments":""}
```

### area接口
* URL  : http://camphorball.dev/elasticsearch/create
* 范例

```
{
    "index":"bzs*",
    "graph":"area",
    "extends":{"calculations":["y1","/","pre"],"countType":"count"},
    "pre":{"type":"must","func":"TermQuery","arguments":["event_type","register"]},
    //"pre": {
    //    "type": "must",
    //    "func": "CardinalityAggregation",
    //    "arguments": [
    //        "pre",
    //        "user_id"
   //     ]
    //},
   "back": {
        "func":"TermsAggregation",
        "arguments":[ "back", "channel_name"],
        "type":"field"
    },
    "id": 19,
    "query":[
             {"type":"must","func":"MatchAllQuery","arguments":[]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-09-01T00:00:00.000Z","to":"2017-09-25T00:00:00.000Z"}] ]}
        ],
    "size":0,
    "aggsX":[{
        "func":"DateHistogramAggregation",
        "name": "x1",
        "arguments": ["x1","created_timestring","1d"],
        "label":""
    }],
    "aggsY":[{
        "func":"SumAggregation",
        "name":"y1",
        "arguments":["y1", "event_data.event_time"],
        "label":"播出总时长"
    },
    {
        "func":"CardinalityAggregation",
        "name":"y2",
        "arguments": ["y2","user_id"],
        "label":"用户总数"
    }],
    "splits":[
    {
        "func":"TermsAggregation",
        "name":"split1",
        "arguments":[ "split1", "channel_name"]
    }
    ]

}
```
* DateHistogramAggregation:参数1 名称，参数2 时间字段，参数3 间隔单位，second=1s，minute=1m，hour=1h，day=1d，month=1M，year=1y
* DateRangeAggregation: 参数1 名称，参数2 时间字段， 参数3 日期格式，参数4，日期数组
* HistogramAggregation:参数1 名称，参数2 字段， 参数3  单位
* FiltersAggregation:参数为对象
* TermsAggregation:参数1 名称 ， 参数2 字段
* SignificantTermsAggregation:参数1 名称， 参数2 字段
* RangeAggregation:参数1 名称， 参数2 字段，参数3 条件数组
* SumAggregation:参数1 名称，参数2 字段
* AvgAggregation:参数1 名称，参数2 字段
* MaxAggregation:参数1 名称， 参数2 字段
* MinAggregation:参数1 名称， 参数2 字段
* PercentilesAggregation: 参数1 名称，参数2 字段
* TopHitsAggregation:参数1 名称，参数2 size，参数3  from，参数4 排序

> 特殊说明:默认情况下must第一个选项填MatchAllQuery，查询所有数据，如果对值由限定要求，则为MatchQuery

### 条件判断
```
{
    "index":"bzs*",
    "graph":"area",
    "id": 19,
    "query":[
             {"type":"must","func":"MatchAllQuery","arguments":[]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-08-01T00:00:00.000Z","to":"2017-08-29T00:00:00.000Z"}] ]}
        ],
    "size":0,
    "aggsX":[
    {
        "func":"DateHistogramAggregation",
        "name": "x1",
        "arguments": ["x1","created_timestring","1d"],
        "label":""
    }],
    "aggsY":[{
        "func":"SumAggregation",
        "name":"y2",
        "arguments": ["y2","event_data.event_time"],
        "label":"播出时长"
    },
    {
        "func":"BucketSelectorAggregation",
        "name":"y1",
        "arguments":["y1",{"y2":"y2"}],
        "condition": {"lang":"expression", "inline":"y2 > 10800"},
        "conditionArguments":["y2",">","10800"]
    }
    ],
    "splits":[
    ]

}
```


### area类型图形
* X轴
```
DateHistogramAggregation
DateRangeAggregation
HistogramAggregation
FiltersAggregation
TermsAggregation
SignificantTermsAggregation
RangeAggregation
```
* y轴
```
SumAggregation
AvgAggregation
MaxAggregation
MinAggregation
PercentilesAggregation
CardinalityAggregation
TopHitsAggregation
```

### TOP排序

```
{
"index":"bzs*",
"graph":"area",
"query":[
    {"type":"must","func":"MatchAllQuery","arguments":[]},
    {"type":"must","func":"RangeQuery","arguments":["created_timestamp",[{"from":"2017-09-01T00:00:00.000Z","to":"2017-09-06T00:00:00.000Z"}] ]}
],
"size":0,
"aggsX":[{
    "func":"DateHistogramAggregation",
    "name":"x1",
    "arguments": ["x1","created_day","1d"]
}],
"aggsY":[
{
    "func":"TopHitsAggregation",
    "name":"y1",
    "arguments": ["y1","1","" ,[{"user_id":{"order":"desc"}}]]
}
]
}
```

### Pie图形
* URL : http://camphorball.dev/elasticsearch/create
* type : POST
* 范例：

```
{
    "index":"bzs*",
    "graph":"pie",
    "id": 19,
    "query":[
             {"type":"must","func":"MatchAllQuery","arguments":[]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-08-01T00:00:00.000Z","to":"2017-08-29T00:00:00.000Z"}] ]}
        ],
    "size":0,
    "aggsX":[],
    "aggsY":[],
    "splits":[
    {
        "func":"TermsAggregation",
        "name":"split1",
        "arguments":[ "split1", "channel_name"]
    }
    ]

}
```

### Map地图
* URL : http://camphorball.dev/elasticsearch/create
* type : POST
* 范例:

```
{
    "index":"bzs*",
    "graph":"map",
    "id": 19,
    "query":[
             {"type":"must","func":"MatchAllQuery","arguments":[]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-08-01T00:00:00.000Z","to":"2017-08-29T00:00:00.000Z"}] ]}
        ],
    "size":0,
    "aggsX":[],
    "aggsY":[],
    "splits":[
    {
        "func":"TermsAggregation",
        "name":"split1",
        "arguments":[ "split1", "geoip.city_name"]
    }
    ]

}
```

### 雷达图

* URL： http://camphorball.dev/elasticsearch/create
* type : POST
* 范例

```
{
    "index":"bzs*",
    "graph":"area",
    "id": 19,
    "query":[
             {"type":"must","func":"MatchAllQuery","arguments":[]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-08-01T00:00:00.000Z","to":"2017-08-29T00:00:00.000Z"}] ]}
        ],
    "size":0,
    "aggsX":[{
        "func":"TermsAggregation",
        "name": "x1",
        "arguments": ["x1","channel_name"],
        "label":""
    }],
    "aggsY":[{
        "func":"AvgAggregation",
        "name":"y1",
        "arguments":["y1", "event_data.event_time"],
        "label":"预估分配"
    },
    {
        "func":"SumAggregation",
        "name":"y2",
        "arguments": ["y2","event_data.event_time"],
        "label":"实际开销"
    }]

}
```

### 数据列表
* URL : http://camphorball.dev/elasticsearch/lists
* type : POST
* 范例 : 


```
{
    "index":"bzs*",
    "graph":"list",
	"sort" : [ [ "user_id",  "desc" ] ],
    "id": 19,
    "query":[
             {"type":"must","func":"MatchAllQuery","arguments":[]},
             {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-08-01T00:00:00.000Z","to":"2017-08-29T00:00:00.000Z"}] ]}
        ],
    "size":10,
   "from":20,
    "aggsX":[],
    "aggsY":[],
	"splits":[]
}
```


### 计算留存
* URL : http://camphorball.dev/elasticsearch/remain
* type : POST
*  范例

```
{
    "index":"bzs-*",
    "graph":"remain",
    "query":[
         {"type":"must","func":"MatchAllQuery","arguments":[]},
         {"type":"must","func":"RangeQuery","arguments":["created_timestring",[{"from":"2017-08-01T00:00:00.000Z","to":"2017-08-05T00:00:00.000Z"}] ]}
    ],
    "size":0,
    "aggsX":[
        {
        "func":"DateHistogramAggregation",
        "name":"x1",
        "arguments": ["x1","created_timestring","1d"],
        "label":"liio"
        }
    ],
    "splits":[
    	{
        "func":"TermsAggregation",
        "name":"split1",
        "arguments":[ "split1", "channel_name"]
    }
    ],
    "space":"1",
    "number":"percent"
}
```
> 说明: number : total | valid | percent 分别为总数，留存数，留存率

### 计算活跃
* URL： http://camphorball.dev/elasticsearch/active
* type :  POST
* 范例

```
{
    "id": 0,
    "index": "bzs-*",
    "graph": "active",
    "space":-2,
    "size": 0,
    "query": [
        {
            "type": "must",
            "func": "MatchQuery",
            "arguments": [
                "event_type",
                "login"
            ]
        },
        {
            "type": "must",
            "func": "RangeQuery",
            "arguments": [
                "created_timestring",
                [
                    {
                        "from": "2017-09-06T17:30:28.000Z",
                        "to": "2017-09-13T17:30:28.000Z"
                    }
                ]
            ]
        }
    ],
    "aggsX": [
        {
            "name": "x1",
            "func": "DateHistogramAggregation",
            "label": "",
            "arguments": [
                "x1",
                "created_timestring",
                "1d"
            ]
        }
    ],
    "aggsY": [
        {
            "name": "y1",
            "func": "CardinalityAggregation",
            "label": "",
            "arguments": [
                "y1",
                "user_id"
            ]
        }
    ],
    "splits": []
}
```

### 主播开播查询
* URL : http://camphorball.dev/elasticsearch/lists
* type : POST
* 范例

```
{
    "id": 0,
    "index": "bzs-*",
    "graph": "anchorList",
    "sort": [
        [
            "created_timestring",
            "desc"
        ]
    ],
    "size": 20,
    "from": 0,
    "query": [
        {
            "type": "must",
            "func": "MatchQuery",
            "arguments": [
                "event_type",
                "stopStreaming"
            ]
        },
        {
            "type": "must",
            "func": "RangeQuery",
            "arguments": [
                "event_data.event_time",
                [
                    {
                        "gt": 0
                    }
                ]
            ]
        },
        {
            "type": "must",
            "func": "RangeQuery",
            "arguments": [
                "created_timestring",
                [
                    {
                        "from": "2017-09-23T17:02:20.000Z",
                        "to": "2017-09-26T17:02:20.000Z"
                    }
                ]
            ]
        }
    ],
    "aggsX": [],
    "aggsY": [],
    "splits": []
}
```

### 在线主播
* URL: http://camphorball.dev/elasticsearch/lists
* type : POST
*  范例

```
{
    "id": 0,
    "index": "bzs-*",
    "graph": "online",
    "size": 10,
    "from":0,
    "query": [
        {
            "type": "must",
            "func": "MatchAllQuery",
            "arguments": []
        },
        {
            "type": "must",
            "func": "RangeQuery",
            "arguments": [
                "created_timestring",
                [
                    {
                        "from": "2017-09-25T00:25:51.000Z",
                        "to": "2017-09-26T18:25:51.000Z"
                    }
                ]
            ]
        }
    ],
    "aggsX": [
        {
            "name": "x1",
            "func": "TermsAggregation",
            "label": "",
            "arguments": [
                "x1",
                "event_data.streaming_id",
                "1000",
                {
                    "_count": "asc"
                }
            ]
        }
    ],
    "aggsY": [
        {
            "name": "y1",
            "func": "CountAggregation",
            "label": "",
            "arguments": [
                "y1",
                null
            ]
        }
    ],
    "splits": []
}
```

### 每日用户使用时长排行榜
* URL : http://camphorball.dev/elasticsearch/create
* type  : POST
* 范例

```
{
    "id": 0,
    "index": "bzs-*",
    "graph": "table",
    "size": 0,
    "query": [
        {
            "type": "must",
            "func": "MatchAllQuery",
            "arguments": []
        },
        {
            "type": "must",
            "func": "RangeQuery",
            "arguments": [
                "created_timestring",
                [
                    {
                        "from": "2017-08-31T10:40:21.000Z",
                        "to": "2017-09-15T10:40:21.000Z"
                    }
                ]
            ]
        }
    ],
    "aggsX": [
        {
            "name": "x1",
            "func": "DateHistogramAggregation",
            "label": "",
            "arguments": [
                "x1",
                "created_timestring",
                "1d"
            ]
        },
        {
            "name": "x2",
            "func": "TermsAggregation",
            "label": "",
            "arguments": ["x2","from_ip",5,[],{"_count":"desc"}]
        }
    ],
    "aggsY": [
        {
            "name": "y1",
            "func": "CountAggregation",
            "label": "",
            "arguments": [
                "y1",
                "event_data.event_time"
            ]
        }
    ],
    "splits": []
}
```

### 获取所有索引
* URL : http://camphorball.dev/elasticsearch/cat/indices
* type :  POST
* 范例 : 

```
{
"username":"admin"
}
```


### mapping
* URL : http://camphorball.dev/elasticsearch/mapping
* type : POST
* 范例 : 
```
{
     "index":"bzs-*"
}
```

# 报表

### Excel报表生成
* URL : http://camphorball.dev/excel/generate
* type : POST
* 范例

```
{
	"tableData": [ ["日期","2017-08-01T00:00:00.000Z","2017-08-02T00:00:00.000Z"], ["平均值","77","99"] ],
	"tableName":"月平均播出时长"
}
```

### Excel报表导出
* URL : http://camphorball.dev/excel/export/9
* type : get

### elasticsearch备份

#####  创建备份目录

```
PUT _snapshot/backup
{
  "type": "fs", 
    "settings": {
        "location": "/data1/elasticsearch-5.4.1/backup"
    }
}
```

##### 按日期备份数据

```
PUT _snapshot/backup/snapshot-2017.09.25
{
  "indices": "bzs-*,apps-*"
}
```

##### 查看备份状态

```
GET _snapshot/backup/_all    查看所有备份情况
GET _snapshot/backup/snapshot-2017.09.25  查看某天备份情况
```

##### 还原数据

```
POST _snapshot/backup/snapshot-2017.09.25/_restore
```
