<?php

return array(
    'VAR_CONTROLLER' => 'c',  //URL中的控制器变量名称
    'VAR_ACTION' => 'a',  //URL中的操作变量名称
    'DEFAULT_CONTROLLER' =>'index',   //默认  控制器  名称
    'DEFAULT_ACTION' =>'index',   //默认 方法 名称
    'EMPTY_ACTION' => '_empty' , //当指定执行的方法不存在时，执行控制器的此方法
    'VAR_PATHINFO' =>'v',   //rewrite  模式下使用的变量名称
    'URL_SUFFIX' =>'.html',  //默认URL后缀
    'CACHE_TIME' => 200 , //缓存 有效时间 ， 以秒为单位 
    'URL_REWRITE' => false,   //开启URL rewrite 模式 ，默认为关闭。。
    'FORM_POST_CHECK' => true, //表单 POST提交 验证，默认开启
    
    //数据 库配置 
    'DB_HOST' =>  '127.0.0.1',
    'DB_USER' => '11',
    'DB_PASS' =>'11',   
    'DB_NAME' =>'11',   
    'DB_PCONNECT' => false,     //是否使用持久连接
    'DB_CHARSET' => 'utf8',   //数据 库编码 
    'DB_PREFIX'             => 'y_',    // 数据库表前缀
    
    //表单验证键的session名
    'FORM_KEY' => 'Iframe_Kyle_316686606@qq.com', //md5('_iframe_kyle_316686606@qq.com'),
    //表单验证键name
    'FORM_KEY_NAME' => '_verifyKey_',
    'EXTTAGLIB'=>false,  //项目中扩展的标签名,默认为false ,没有扩展
    'FORMNOVERIFYURL' => array(),   //检测当前链接是否不需要验证
    
    
    'tag_begin' => '<',      //模板文件中 tag  开始标签 
    'tag_end'=> '>',        //模板文件中 tag  结束标签 
    'var_begin' => '{',       //模板变量开始标签
    'var_end' => '}',        //模板变量结束标签
    'template_suffix' => '.html',        //伪静态文件后缀
    'cachefile_suffix' => '.php',        //缓存 文件 后缀
    'layout_item' => '{__CONTENT__}',    //布局模板中被替换的内容标签
    
    'YixinuVersion' =>'1.0',
    'iframeVerison' =>'1.0',
);