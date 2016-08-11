<?php
/**
 * 有用的函数  算法     经验   学习文件
 * Some rights reserved：www.thinkcmf.com


 */

//头部搜索 好用奇妙的算法

$fields = array(
    'start_time' => array("field" => "post_date", "operator" => ">"),
    'end_time' => array("field" => "post_date", "operator" => "<"),
    'keyword' => array("field" => "post_title", "operator" => "like"),
);
if (IS_POST) {
    foreach ($fields as $param => $val) {
        if (isset($_POST[$param]) && !empty($_POST[$param])) {
            $operator = $val['operator'];
            $field = $val['field'];
            $get = $_POST[$param];
            $_GET[$param] = $get;
            if ($operator == "like") {
                $get = "%$get%";
            }
            array_push($where_ands, "$field $operator '$get'");
        }
    }
} else {
    foreach ($fields as $param => $val) {
        if (isset($_GET[$param]) && !empty($_GET[$param])) {
            $operator = $val['operator'];
            $field = $val['field'];
            $get = $_GET[$param];
            if ($operator == "like") {
                $get = "%$get%";
            }
            array_push($where_ands, "$field $operator '$get'");
        }
    }
}

//end///////////////////////////////////////////////////////////

//获取 关键字 函数 $num 为关键字的数量
function getKeys($str,$num){
    $i=0;
    $key=array();
    $str=trim($str);
    for($i;$i< $num;$i++){
        $pos=strpos($str," ");
        if($pos == ""){
            $key[$i]=$str;
            return $key;
        }else{
            $key[$i]=substr($str,0,$pos);
            $key_remain=substr($str,$pos);
            $str=ltrim($key_remain);
        }
    }
    return $key;
}
///////////////
//$name  为搜索的字段 $page 为搜索数据的当前页
function  getCurrentPage(){

    $name="";
    $searchData= M("Institution")->where("name='$name' and parentid <> 0")->find();
    if(!$searchData){
        echo 0;
        exit;
    }
    $parentid=$searchData["parentid"];
    $data=M("Institution")->where("parentid='$parentid'")->select();
    $count=count($data);
    while ($serach = current($data)) {
        $result=array_diff($searchData,$serach);
        if(!$result){
            $key1=key($data);
        }
        next($data);
    }
    $page=ceil(($key1+1)/4);
}


/**
 * 获取PDF文件页数的函数获取
 * 文件应当对当前用户可读（linux下）
 * @param  [string] $path [文件路径]
 * @return [array]        [数组第一位表示成功与否，第二位表示提示信息]
 */
function getPdfPages($path){
    if(!file_exists($path)) return array(false,"文件\"{$path}\"不存在！");
    if(!is_readable($path)) return array(false,"文件\"{$path}\"不可读！");
    // 打开文件
    $fp=@fopen($path,"r");
    if (!$fp) {
        return array(false,"打开文件\"{$path}\"失败");
    }else {
        $max=0;
        while(!feof($fp)) {
            $line = fgets($fp,255);
            if (preg_match('/\/Count [0-9]+/', $line, $matches)){
                preg_match('/[0-9]+/',$matches[0], $matches2);
                if ($max<$matches2[0]) $max=$matches2[0];
            }
        }
        fclose($fp);
        // 返回页数
        return array(true,$max);
    }
}
/**
 * 原生php 调用存储过程
 */

  public function query1(){

    define('CLIENT_MULTI_RESULTS', 131072);
    $conn = mysql_connect('192.168.10.226:3306', 'root', 'hengda', 1, CLIENT_MULTI_RESULTS);
    $db = mysql_select_db("thinkcmf", $conn);
    $data = mysql_query("CALL thinkcmf.getAll(4)", $conn);
    while($row = mysql_fetch_assoc($data))
    {
        $showResult[] = $row;
    }
    mysql_close($conn);
    print_r($showResult);

    exit;
  }

public function  format($data,$item1,$item2){
    $arr=array();
    foreach($data as $key=>$row){
        $arr[$row[$item1]]=$row[$item2];
    }
    return $arr;
}

/**
 * 生成多个不重复的随机数字
 * @param  int $start  需要生成的数字开始范围
 * @param  int $end    结束范围
 * @param  int $len    生成数字个数
 * @return  array 生成的随机数
 *
 * */
function get_rand_number($start,$end,$len){

    $conn=0;
    $temp=array();
    while($conn<$len){
        $temp[]=mt_rand($start,$end);
        $data=array_unique($temp);
        $conn=count($data);
    }
    sort($data);
    return $data;
}

/**
 * 获得当前页面的url
 *
 * */
function getUrl(){
    $url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    return $url;
}

//测试文件夹是否可写
function dir_writeable($dir) {
    if(!is_dir($dir)) {
        @mkdir($dir, 0777);
    }
    if(is_dir($dir)) {
        if($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    }
    return $writeable;
}

/**
 * 模拟post 请求
 * */
function  curlPost(){
    $curlPost = array(
        'username' => 'admin',
        'password' => 'hengda'
    );
    $cookie_file = tempnam(APP_PATH, 'cookie');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://192.168.10.226/index.php?g=user&m=login&a=dologin");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlPost));
   // curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 其中$param 可以为json格式

    curl_exec($ch);
    curl_close($ch);



    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://192.168.10.226/index.php?g=user&m=center&a=relationship");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //读取cookie
    $rs = curl_exec($ch); //执行cURL抓取页面内容
    curl_close($ch);


}



