<?php

/**
 *  Description：分页
 *  @author  Kyle  <316686606@qq.com>
 */

/**
 * css 样式 
 * 
    #page {
        float:right;
        display:inline;
        margin:0 250px 12px 0;
        font: 12px/16px arial;
        text-align: center;
    }
    #page a {
        float:left;
        margin: 0 3px;
        padding: 3px 9px;
        color: #666666;
        text-decoration: none;
        display: inline-block;
        border: 1px solid #DDDDDD;
        background:#FFFFFF;
    }
    #page a.now_page, #page a:hover {
        color: #FFFFFF;
        background:#0055CC;
    }
 *
 */


/**
 * 调用方式 
 * 
    $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : 1;
 * $listrows = 15;
 * $count = $count;
    $params = array(
        'total_rows' => $count,
        'parameter' => '&p=00000',  //后面5个0是替换页码的字符
        'now_page' => $p,
        'list_rows' => $listrows,
    );
    $page = new page($params);
    $pageinfo = $page->show(4);
 */

namespace lib;    
class page {

    public $first_row;     //起始行数
    public $list_rows;     //列表每页显示行数
    public $total_pages;   //总页数
    public $total_rows;    //总行数
    public $now_page;         //当前页数
    public $method = 'defalut'; //处理情况 Ajax分页 Html分页(静态化时) 普通get方式 
    public $parameter = '';
    public $page_name;        //分页参数的名称
    public $ajax_func_name;
    public $plus = 3;         //分页偏移量
    public $url;

    /**
     * 构造函数
     * 
     * @param Array $data
     * @param String $page['parameter']  分页的链接  当前面替换符号为 #?
     * @param String $page['pagestyle']  分页样式，参数：1、2、3
     * @param String $page['method']    处理情况 Ajax分页 Html分页(静态化时) 普通get方式 ，参数：html ajsx 默认为get方式 
     * @param int $page['now_page] 当前分页参数
     * @param int $page['list_rows] 一页显示多少条数据
     * 
     */
    public function __construct($data = array()) {
        $this->total_rows = $data['total_rows'];
        $this->parameter = !empty($data['parameter']) ? $data['parameter'] : '&p=00000';
        $this->list_rows = $data['list_rows'];//!empty($data['list_rows']) && $data['list_rows'] <= 100 ? $data['list_rows'] : 15;
        $this->total_pages = ceil($this->total_rows / $this->list_rows);
        $this->page_name = !empty($data['page_name']) ? $data['page_name'] : 'p';
        $this->ajax_func_name = !empty($data['ajax_func_name']) ? $data['ajax_func_name'] : '';
        $this->method = !empty($data['method']) ? $data['method'] : '';


        /* 当前页面 */
        if (!empty($data['now_page'])) {
            $this->now_page = intval($data['now_page']);
        } else {
            $this->now_page = !empty($_GET[$this->page_name]) ? intval($_GET[$this->page_name]) : 1;
        }
        $this->now_page = $this->now_page <= 0 ? 1 : $this->now_page;


        if (!empty($this->total_pages) && $this->now_page > $this->total_pages) {
            $this->now_page = $this->total_pages;
        }
        $this->first_row = $this->list_rows * ($this->now_page - 1);
    }

    /**
     * 得到当前连接
     * @param $page
     * @param $text
     * @return string
     */
    protected function _get_link($page, $text) {
        switch ($this->method) {
            case 'ajax':
                $parameter = '';
                if ($this->parameter) {
                    $parameter = ',' . $this->parameter;
                }
                return '<a onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "";
                break;

            case 'html':
                $url = str_replace('00000', $page, $this->parameter);
                return '<a href="' . $url . '">' . $text . '</a>' . "";
                break;

            default:
                return '<a href="' . $this->_get_url($page) . '">' . $text . '</a>' . "";
                break;
        }
    }
    
    
    protected function _get_cur_link($page, $text) {
        switch ($this->method) {
            case 'ajax':
                $parameter = '';
                if ($this->parameter) {
                    $parameter = ',' . $this->parameter;
                }
                return '<a class="now_page" onclick="' . $this->ajax_func_name . '(\'' . $page . '\'' . $parameter . ')" href="javascript:void(0)">' . $text . '</a>' . "";
                break;

            case 'html':
                $url = str_replace('00000', $page, $this->parameter);
                return '<a class="now_page" href="' . $url . '">' . $text . '</a>' . "";
                break;

            default:
                return '<a class="now_page" href="' . $this->_get_url($page) . '">' . $text . '</a>' . "";
                break;
        }
    }

    /**
     * 设置当前页面链接
     */
    protected function _set_url() {
        $url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '' : "?") . $this->parameter;
        $parse = parse_url($url);
        if (isset($parse['query'])) {
            parse_str($parse['query'], $params);
            unset($params[$this->page_name]);
            $url = $parse['path'] . '?' . http_build_query($params);
        }
        if (!empty($params)) {
            $url .= '&';
        }
        $this->url = $url;
    }

    /**
     * 得到$page的url
     * @param $page 页面
     * @return string
     */
    protected function _get_url($page) {
        if ($this->url === NULL) {
            $this->_set_url();
        }
        //	$lable = strpos('&', $this->url) === FALSE ? '' : '&';
        return $this->url . $this->page_name . '=' . $page;
    }

    /**
     * 得到第一页
     * @return string
     */
    public function first_page($name = '第一页') {
        if ($this->now_page > 5) {
            return $this->_get_link('1', $name);
        }
        return '';
    }

    /**
     * 最后一页
     * @param $name
     * @return string
     */
    public function last_page($name = '最后一页') {
        if ($this->now_page < $this->total_pages - 5) {
            return $this->_get_link($this->total_pages, $name);
        }
        return '';
    }

    /**
     * 上一页
     * @return string
     */
    public function up_page($name = '上一页') {
        if ($this->now_page != 1) {
            return $this->_get_link($this->now_page - 1, $name);
        }
        return '';
    }

    /**
     * 下一页
     * @return string
     */
    public function down_page($name = '下一页') {
        if ($this->now_page < $this->total_pages) {
            return $this->_get_link($this->now_page + 1, $name);
        }
    }

    /**
     * 分页样式输出
     * @param $param
     * @return string
     */
    public function show($param = 1) {
        if ($this->total_rows < 1) {
            return '';
        }

        $className = 'show_' . $param;

        $classNames = get_class_methods($this);

        if (in_array($className, $classNames)) {
            return $this->$className();
        }
        return '';
    }
    
    /**
     * 得到第一页
     * @return string
     */
    public function first_pageExt($name = '第一页') {
        if ($this->now_page >= 6) {
            return $this->_get_link('1', $name).'<a>...</a>';
        }
        return '';
    }

    /**
     * 最后一页
     * @param $name
     * @return string
     */
    public function last_pageExt($name = '最后一页') {
        if ($this->now_page <= $this->total_pages - 5) {
            return '<a>...</a>'.$this->_get_link($this->total_pages, $name);
        }
        return '';
    }
    
    protected function show_4() {
        $plus = 4;//$this->plus;
        if ($plus + $this->now_page > $this->total_pages) {
            $begin = $this->total_pages - $plus * 2;
        } else {
            $begin = $this->now_page - $plus;
        }

        $begin = ($begin >= 1) ? $begin : 1;
        $return = '';
        $return .= $begin==1 ? null : $this->first_pageExt();
        for ($i = $begin; $i <= $begin + $plus * 2; $i++) {
            $end = $i;
            if ($i > $this->total_pages) {
                break;
            }
            if ($i == $this->now_page) {
                $return .= $this->_get_cur_link($i, $i) . ""; //"<a class='now_page'>$i</a>";
            } else {
                $return .= $this->_get_link($i, $i) . "";
            }
        }
        $return .= $end >= $this->total_pages ? null : $this->last_pageExt();
        $return .= '  '.$this->total_rows . '条记录，共' . $this->total_pages . '页';//, 当前第 ' . $this->now_page . ' 页 ';
        return $return;
    }

    protected function show_2() {
        if ($this->total_pages != 1) {
            $return = '';
            $return .= $this->up_page('<span class=\'up\'></span>');
            for ($i = 1; $i <= $this->total_pages; $i++) {
                if ($i == $this->now_page) {
                    $return .= "<a class='now_page'>$i</a>";
                } else {
                    if ($this->now_page - $i >= 4 && $i != 1) {
                        $return .="<a><span class='pageMore'>...</span></a>";
                        $i = $this->now_page - 3;
                    } else {
                        if ($i >= $this->now_page + 5 && $i != $this->total_pages) {
                            $return .="<a><span>...</span></a>";
                            $i = $this->total_pages;
                        }
                        $return .= $this->_get_link($i, $i) . "";
                    }
                }
            }
            $return .= $this->down_page('<span class=\'down\'></span>');
            return $return;
        }
    }

    protected function show_1() {
        $plus = $this->plus;
        if ($plus + $this->now_page > $this->total_pages) {
            $begin = $this->total_pages - $plus * 2;
        } else {
            $begin = $this->now_page - $plus;
        }

        $begin = ($begin >= 1) ? $begin : 1;
        $return = '';
        $return .= $this->first_page();
        $return .= $this->up_page();
        for ($i = $begin; $i <= $begin + $plus * 2; $i++) {
            if ($i > $this->total_pages) {
                break;
            }
            if ($i == $this->now_page) {
                $return .= "<a class='now_page'>$i</a>";
            } else {
                $return .= $this->_get_link($i, $i) . "";
            }
        }
        $return .= $this->down_page();
        $return .= $this->last_page();
        return $return;
    }

    protected function show_3() {
        $plus = $this->plus;
        if ($plus + $this->now_page > $this->total_pages) {
            $begin = $this->total_pages - $plus * 2;
        } else {
            $begin = $this->now_page - $plus;
        }
        $begin = ($begin >= 1) ? $begin : 1;
//        $return = '共 ' .$this->total_rows . ' 个记录分为 ' . $this->total_pages . ' 页, 当前第 ' . $this->now_page . ' 页 ';
        $return =  $this->total_rows . ' 个记录,共' . $this->total_pages . '页' ;
        $return .= ',每页 ';
        $return .= '<input type="text" value="' . $this->list_rows . '" id="pageSize" size="3">个记录, ';
        $return .= $this->_get_link('1', '第一页'); //$this->first_page() . "";
        $return .= $this->up_page() . "";
        $return .= $this->down_page() . "";
        $return .= $this->last_page() . "";
        $return .= '<select onchange="' . $this->ajax_func_name . '(this.value)" id="gotoPage">';

        for ($i = $begin; $i <= $begin + 10; $i++) {
            if ($i > $this->total_pages) {
                break;
            }
            if ($i == $this->now_page) {
                $return .= '<option selected="true" value="' . $i . '">' . $i . '</option>';
            } else {
                $return .= '<option value="' . $i . '">' . $i . '</option>';
            }
        }
        $return .= '</select>';
        return $return;
    }

    /**
     * ajax  手机 端 调用 的翻页 
     * @return string
     */
    protected function show_5() {
        $plus = $this->plus;
        if ($plus + $this->now_page > $this->total_pages) {
            $begin = $this->total_pages - $plus * 2;
        } else {
            $begin = $this->now_page - $plus;
        }
        $return = $this->down_page('加载更多…') . "";
        return $return;
    }

}

?>
