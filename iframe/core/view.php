<?php

/**
 * 框架视图类
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace core;

class view {

    //模板变量
    protected $tplvar = array();
    protected $cfg = array();
    protected $redisvar = array();

    public function __construct() {
        $this->cfg['tag_begin'] = '<';//cfg('tag_begin');       //模板文件中 tag  开始标签 
        $this->cfg['tag_end'] = '>';//cfg('tag_end');        //模板文件中 tag  结束标签 
        $this->cfg['var_begin'] = '{';//cfg('var_begin');       //模板变量开始标签
        $this->cfg['var_end'] = '}';//cfg('var_end');        //模板变量结束标签
        $this->cfg['template_suffix'] = '.html';//cfg('template_suffix');        //模板 文件后缀
        $this->cfg['cachefile_suffix'] = '.php';//cfg('cachefile_suffix');        //缓存 文件 后缀
        $this->config['layout_item'] = '{__CONTENT__}';//cfg('layout_item');    //布局模板中被替换的内容标签
    }

    /**
     * 显示模板
     * @param type $templateFile
     * @return boolean
     */
    public function display($templateFile = '') {
        if (empty($templateFile)) {
            return false;
        }

        $fileName = VIEW_PATH . $templateFile . $this->cfg['template_suffix']; //默认使用后缀  html
        if (file_exists($fileName) == false) {
            $fileName = VIEW_PATH . $templateFile . $this->cfg['cachefile_suffix'];  //如果不存在则使用 php
        }
        $string = md5($fileName);
        $cacheFile = CACHE_PATH . $string . $this->cfg['cachefile_suffix'];

        //如果缓存文件有效，并且没开启调试模式，直接读取缓存
        if ($this->checkcache($cacheFile) && APP_DEBUG == false) {
            //模板变量解析为PHP独立变量，使其可以在模板文件中直接调用
            extract($this->tplvar, EXTR_OVERWRITE);
            include $cacheFile;
        } else {
            $this->Compiler($fileName, $cacheFile);
        }
    }

    /**
     * 模板变量赋值
     * @param type $name
     * @param type $value
     */
    public function assign($name, $value = '') {
        if (is_array($name)) {
            $this->tplvar = array_merge($this->tplvar, $name);
        } else {
            $this->tplvar[$name] = $value;
        }
    }

    /**
     * 编译模板，生成缓存 并加载 。
     * @param string $fileName  模板文件全路径
     * @param string $cacheFile 缓存文件全路径
     */
    public function Compiler($fileName, $cacheFile) {
        if (is_file($fileName)) {
            $tplContent = file_get_contents($fileName);
            //内容不能为空。
            if (empty($tplContent))
                exit('The content of the template is empty ! ');

            /**
             * ----- start -----
             * 判断是否设置了模板布局，判断条件为模板文件中有一个标签：
             * <layout name="layout/template" replace="__CONTENT__" />  or <layout name="template" />
             * layout 是项目 computer 目录下的  dir
             * replace  是布局模板 文件 中的设置 的被替换标签内容 
             * template 为布局模板文件名
             */
            $pattern = '/' . $this->cfg['tag_begin'] . 'layout\s(.+?)\s*?\/' . $this->cfg['tag_end'] . '/is';
            $find = preg_match($pattern, $tplContent, $matches);
            if ($find) {
                //删除 layout 标签
                $tplContent = str_replace($matches[0], '', $tplContent);
                $array = $this->parseXmlAttrs($matches[1]);

                $layoutFile = VIEW_PATH . $array['name'] . $this->cfg['template_suffix'];
                if (file_exists($layoutFile) == false) {
                    $layoutFile = VIEW_PATH . $array['name'] . $this->cfg['cachefile_suffix'];  //如果不存在则使用 php
                }

                $replace = isset($array['replace']) ? $array['replace'] : $this->config['layout_item'];
                // 替换布局的主体内容

                $layoutContent = file_get_contents($layoutFile);
                $tplContent = str_replace($replace, $tplContent, $layoutContent);
            }
            /**
             * ----- end -----
             */
            /**
             * 解析模板 include 标签
             * <include file='include/head' />
             */
            $tplContent = $this->parseInclude($tplContent);

            //解析 form表单，添加 验证 码
//            $tplContent = $this->parseForm($tplContent);
            //解析模板
            $tplContent = $this->parseTpl($tplContent);
            //解析项目中扩展的标签
            if (cfg('EXTTAGLIB') != false) {
                $tplContent = $this->extTaglib($tplContent);
            }
            //去掉模板中的html注释
            $tplContent = preg_replace("/<!--[^\[^i^f][\s\S]*?[^i^f^\]]-->/", "", $tplContent);

            //删除注释  ( 两斜杠加两横 //-- )
            $tplContent = preg_replace("/\/\/--.*[\r|\n]/", "\n", $tplContent);
            //删除注释  (/*  */)
            $tplContent = preg_replace("/\/\*.*\*\//", "\n", $tplContent);

            //删除空白行和空格，压缩代码
            $tplContent = preg_replace("/[\n\r\n\t]\s*/", " ", $tplContent);
            //写入缓存文件
            if (is_file($cacheFile))
                unlink($cacheFile);
            file_put_contents($cacheFile, $tplContent);

            //模板变量解析为PHP独立变量，使其可以在模板文件中直接调用
            extract($this->tplvar, EXTR_OVERWRITE);
            include $cacheFile;
        }else {
            return false;
        }
    }

    //解析 form表单，添加 验证 码
    public function parseForm($content) {
        $pattern = '/' . $this->cfg['tag_begin'] . 'form\s?(.+?)\s?' . $this->cfg['tag_end'] . '\s?(.*?)\s?' . $this->cfg['tag_begin'] . '\/form' . $this->cfg['tag_end'] . '/is';
//        $replacement = "\$this->tagReplace3('$1','$2')";
        $content = preg_replace($pattern, 'self::tagReplace3', $content);

        //将生成的 form 验证  保存到 redis  ----  start
        if (count($this->redisvar) > 0) {
            $redis = new \Redis();
            if (!defined('REDIS_ADDR') || !defined('REDIS_PORT')) {
                msg('redis服务器未定义');
            }
            $redisConnectRes = $redis->connect(REDIS_ADDR, REDIS_PORT);
            if (!$redisConnectRes) {
                msg('redis服务器未定义');
            } //如果REDIS连接失败
            $arr = array();  //将当前页面生成的所有的键名保存在 这个数组中，然后存入redis
            foreach ($this->redisvar as $k => $v) {
                $redis->setex(REDIS_PRE . $v['name'], 3600, $v['value']);
                $arr[] = $v['name'];
            }
            $redisKeyName = md5(session_id());
            //需要把之前保存的复制过来，否则第二次显示会替换掉之前的  start
            $arr2 = json_decode($redis->get(REDIS_PRE . $redisKeyName), 1);  //从redis取出验证KEY的数组
            if (is_array($arr2) && count($arr2) > 0) {
                $arr = array_merge($arr2, $arr);
            }
            //需要把之前保存的复制过来，否则第二次显示会替换掉之前的  end
            $redis->setex(REDIS_PRE . $redisKeyName, 3600, json_encode($arr));
        }
        //将生成的 form 验证  保存到 redis  ----  end

        return $content;
    }

    public function tagReplace3($matches) {
        $content = $matches[2];
        $attr = $matches[1];
        
        $content_ = stripslashes($content);
        //把正常的／换回来
        $content = str_replace('\\', '\\\\', $content_);
        //双引号替换成单引号
        $content = str_replace('\"', '\'', $content);
        $attr_ = stripslashes($attr);
        $attr = str_replace('\"', '\'', $attr_);

        //将生成的 form 验证  保存到 redis  ----  start
        $URL = $_SERVER["REQUEST_URI"];
        $keyvalue = rand(100000, 999999) . $URL;
        $arr = array(
            'name' => getabc(),
            'value' => $keyvalue
        );
        $this->redisvar = array_merge(array($arr), $this->redisvar);
        //将生成的 form 验证  保存到 redis  ----  end

        $str = null;
        $str .= "<form $attr>";
        $str .= $content;
        $str .= "<input type='hidden' name='$arr[name]' value='" . md5($arr['value']) . "' />";
        unset($keyvalue);
        $str .= "</form>";
        return $str;
    }

    /**
     * 解析模板 
     * @param type $content   模板文件内容 
     */
    public function parseTpl($content) {
        //替换模板变量的标签
        $pattern = '/' . $this->cfg['var_begin'] . '\$(.+?)' . $this->cfg['var_end'] . '/is';
        $replacement = 'self::tagReplace';
        $content = preg_replace_callback($pattern, $replacement, $content);

        //替换模板中使用函数的标签
        $pattern_fun = '/' . $this->cfg['var_begin'] . 'fun(.+?)' . $this->cfg['var_end'] . '/is';
        $replacement_fun = "<?php echo @$1; ?>";
        $content = preg_replace($pattern_fun, $replacement_fun, $content);

        //替换模板中的U标签
        $pattern_u = '/' . $this->cfg['var_begin'] . 'u\s+(.+?)\s*' . $this->cfg['var_end'] . '/is';
        $replacement_u = "<?php echo @u(\"$1\"); ?>";
        $content = preg_replace($pattern_u, $replacement_u, $content);
        //替换模板中的U标签
        $pattern_createLink = '/' . $this->cfg['var_begin'] . 'createLink\s+(.+?)\s*' . $this->cfg['var_end'] . '/is';
        $replacement_createLink = "<?php echo @createLink($1); ?>";
        $content = preg_replace($pattern_createLink, $replacement_createLink, $content);

        //解析框架中定义的标签
        $taglib = new taglib();
        $tags = $taglib->getall();
        foreach ($tags as $name => $value) {
            global $tmp_name;  //使用全局变量，要不然preg_replace_callback 回调函数访问不了
            $tmp_name = $name;
            if ($value['type'] == 1) {
                $pattern = '/' . $this->cfg['tag_begin'] . $name . '\s?(.+?)\s?' . $this->cfg['tag_end'] . '\s?(.*?)\s?' . $this->cfg['tag_begin'] . '\/' . $name . $this->cfg['tag_end'] . '/is';
                //如果没有找到标签则跳出。
                if (preg_match($pattern, $content) == 0) {
                    continue;
                }
//                $replacement = "\$this->tagReplace2('$name','$1','$2')";
                $content = preg_replace_callback($pattern, function($matches) {
                    global $tmp_name; //使用全局变量，要不然preg_replace_callback 回调函数访问不了
                    $matches[999] = $tmp_name;
                    return self::tagReplace2($matches);
                }, $content);
            } elseif ($value['type'] == 2) {
                $pattern = '/' . $this->cfg['tag_begin'] . $name . '\s?(.*?)\s?\/' . $this->cfg['tag_end'] . '/is';
                //如果没有找到标签则跳出。
                if (preg_match($pattern, $content) == 0) {
                    continue;
                }
//                $replacement = "\$this->tagReplace2('$name','$1','')";
//                $content = preg_replace($pattern, $replacement, $content);
                $content = preg_replace_callback($pattern, function($matches) {
                    global $tmp_name; //使用全局变量，要不然preg_replace_callback 回调函数访问不了
                    $matches[999] = $tmp_name;
                    $matches[2] = '';
                    return self::tagReplace2($matches);
                }, $content);
            }
        }
        return $content;
    }

    public function tagReplace2($matches) {
        //取标签内容
        $tag = $matches[999];
        $attr = $matches[1];
        $content = $matches[2];
        
        $taglib = new taglib();
        $tag = '_' . $tag;
        if (!empty($attr)) {
            //为了替换模板会出错，把双引号替换成单引号
            $attr = stripslashes($attr);
            $attr_ = str_replace('\"', '\'', $attr);
            $attr = $this->parseXmlAttrs($attr_);
        }
        if (method_exists($taglib, $tag)) {
            //为了替换模板会出错，把双引号替换成单引号
            $content = stripslashes($content);
            $content = str_replace('\"', '\'', $content);
            //去掉空格空行    如果加上下面两行，会出现不能删除注释的情况 
//            $content = preg_replace('/[\n|\r\n]\s?/', '', $content);
//            $content = preg_replace('/\s+/', ' ', $content);
            //执行对应的标签函数 
            $content = $taglib->$tag($attr, $content);
            return $content;
        } else {
            return $content;
        }
    }

    /**
     * 解析项目中定义的标签 
     * @param type $content   模板文件内容 
     */
    public function extTaglib($content) {
        //解析框架中定义的标签
        $taglib = new \models\taglib();
        $tags = $taglib->getall();
        foreach ($tags as $name => $value) {
            global $tmp_name;  //使用全局变量，要不然preg_replace_callback 回调函数访问不了
            $tmp_name = $name;
            if ($value['type'] == 1) {
                $pattern = '/' . $this->cfg['tag_begin'] . $name . '\s?(.+?)\s?' . $this->cfg['tag_end'] . '\s?(.*?)\s?' . $this->cfg['tag_begin'] . '\/' . $name . $this->cfg['tag_end'] . '/is';
//                $replacement = "\$this->extTaglibReplace('$name','$1','$2')";
//                $content = preg_replace($pattern, $replacement, $content);
                $content = preg_replace_callback($pattern, function($matches) {
                    global $tmp_name; //使用全局变量，要不然preg_replace_callback 回调函数访问不了
                    $matches[999] = $tmp_name;
                    return self::extTaglibReplace($matches);
                }, $content);
            } elseif ($value['type'] == 2) {
                $pattern = '/' . $this->cfg['tag_begin'] . $name . '\s?(.*?)\s?\/' . $this->cfg['tag_end'] . '/is';
//                $replacement = "\$this->extTaglibReplace('$name','$1','')";
//                $content = preg_replace($pattern, $replacement, $content);
                $content = preg_replace_callback($pattern, function($matches) {
                    global $tmp_name; //使用全局变量，要不然preg_replace_callback 回调函数访问不了
                    $matches[999] = $tmp_name;
                    $matches[2] = '';
                    return self::extTaglibReplace($matches);
                }, $content);
            }
        }
        return $content;
    }

    public function extTaglibReplace($matches) {
        //取标签内容
        $tag = $matches[999];
        $attr = $matches[1];
        $content = $matches[2];
        
        $taglib = new \models\taglib();
        $tag = '_' . $tag;
        if (!empty($attr)) {
            //为了替换模板会出错，把双引号替换成单引号
            $attr = stripslashes($attr);
            $attr_ = str_replace('\"', '\'', $attr);
            $attr = $this->parseXmlAttrs($attr_);
        }
        if (method_exists($taglib, $tag)) {
            //为了替换模板会出错，把双引号替换成单引号
            $content = stripslashes($content);
            $content = str_replace('\"', '\'', $content);
            //去掉空格空行
//            $content = preg_replace('/[\n|\r\n]\s?/', '', $content);
//            $content = preg_replace('/\s+/', ' ', $content);
            //执行对应的标签函数 
            $content = $taglib->$tag($attr, $content);
            //合并变量
            $this->tplvar = array_merge($this->tplvar, $taglib->tplvar);
            return $content;
        } else {
            return $content;
        }
    }

    public function tagReplace($matches) {
        $name = $matches[1];
        //替换二维数组
        $pattern = '/^(.+?)\.(.+?)$/i';
        $replacement = '$1[\'$2\']';
        $name = preg_replace($pattern, $replacement, $name);
        return '<?php if(isset($' . $name . ')):echo $' . $name . '; endif; ?>';
    }

    /**
     * 解析模板中的变量
     * @param type $content
     */
    public function parseVar($content) {
        return $content;
    }

    /**
     * 解析模板中的 include 标签
     * @param type $content
     * @return type
     */
    public function parseInclude($content) {
        // 读取模板中的include标签
        $find = preg_match_all('/' . $this->cfg['tag_begin'] . 'include\s(.+?)\s*?\/' . $this->cfg['tag_end'] . '/is', $content, $matches);
        if ($find) {
            for ($i = 0; $i < $find; $i++) {
                $include = $matches[1][$i];
                $array = $this->parseXmlAttrs($include);
                $file = $array['file'];
                unset($array['file']);
                $content = str_replace($matches[0][$i], $this->parseIncludeItem($file, $array), $content);
            }
        }
        return $content;
    }

    /**
     * 加载公共模板并缓存 和当前模板在同一路径，否则使用相对路径
     * @param string $tmplPublicName  公共模板文件名
     * @param array $vars  要传递的变量列表
     * @return string
     */
    private function parseIncludeItem($tmplPublicName, $vars = array()) {
        // 分析模板文件名并读取内容
        $file_ = VIEW_PATH . $tmplPublicName . $this->cfg['template_suffix'];
        $parseStr = file_get_contents($file_);
        // 替换变量
        foreach ($vars as $key => $val) {
            $parseStr = str_replace('[' . $key . ']', $val, $parseStr);
        }
        // 再次对包含文件进行模板分析
        return $this->parseInclude($parseStr);
    }

    /**
     * 返回 标签属性，用XML方式解析 
     * @param string $attrs  属性字符串
     * @return array
     */
    public function parseXmlAttrs($attrs) {
        $xml = '<tpl><tag ' . $attrs . ' /></tpl>';
        $xml = simplexml_load_string($xml);
        if (!$xml) {
            exit('Parse error ! ');
        }
        $xml = (array) ($xml->tag->attributes());
        $array = array_change_key_case($xml['@attributes']);
        return $array;
    }

    /**
     * 
     * @param type $filename  缓存文件完整路径。
     * @return boolean
     */
    public function checkcache($filename) {
        if (!is_file($filename)) {
            return false;
        }
        $time_ = cfg('CACHE_TIME') + filemtime($filename);
        if ((int) $time_ < time()) {
            return false;
        }
        return true;
    }

}
