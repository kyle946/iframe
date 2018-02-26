<?php

/**
 * 标签解析类
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace core;
class taglib {
    
    protected $tags = array(
        //attr 属性列表 ，type 标签类型：1为<list></list>； 2为<list />
        'list'=>array('attr'=>'name,item','type'=>1),
        'if'=>array('attr'=>'con','type'=>1),
        'elseif'=>array('attr'=>'con','type'=>2),
        'else'=>array('attr'=>'','type'=>2),
        'case'=>array('attr'=>'value','type'=>1),
        'default'=>array('attr'=>'','type'=>2),
        'switch'=>array('attr'=>'name','type'=>1),
        'root'=>array('attr'=>'','type'=>2),   //网站根路径 标签 
    );
    
    public function _switch($attr,$content) {
        //替换掉switch 与 case 之间的空格 ，如果 有空间会报语言错误 
        $content = preg_replace('/\s+<\?/', '<?', $content);
        
        $name = $attr['name'];
        $str_ = "<?php  switch($$name): ?>";
        $str = str_replace('$$', '$', $str_);
        $str.=$content;
        $str.="<?php endswitch; ?>";
        return $str;
    }
    
    public function _root($attr,$content) {
        return __ROOT__;
    }
    
    public function _case($attr,$content) {
        $value = $attr['value'];
        $str = "<?php case '$value': ?>";
        $str.=$content;
        $str.="<?php break; ?>";
        return $str;
    }
    
    public function _default($attr,$content) {
        $str = "<?php default : ?>";
        return $str;
    }
    
    public function _if($attr,$content) {
        $con = '@'.$attr['con'];
        $str = "<?php if($con): ?>";
        $str.=$content;
        $str.="<?php endif; ?>";
        return $str;
    }
    
    public function _elseif($attr,$content) {
        $con = '@'.$attr['con'];
        $str = "<?php elseif($con): ?>";
        return $str;
    }
    
    public function _else($attr,$content) {
        $str = "<?php else: ?>";
        return $str;
    }
    
    public function _list($attr,$content) {
        $name = $attr['name'];
        $item = $attr['item'];
        
        $str = "<?php if(isset($$name) && is_array(\$$name)):";
        $str .= "foreach(\$$name as \$key=>\$$item){ ?>";
        $str.=$content;
        $str.="<?php } endif; ?>";
        return $str;
    }


    /**
     * 获取所有定义的标签
     */
    public function getall() {
        return $this->tags;
    }
}
