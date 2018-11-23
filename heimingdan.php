<?php
header("Access-Control-Allow-Origin:*");
@$referrer = $_GET['referrer'] ? $_GET['referrer'] : 0;
$heimingdan = new Heimingdan();

$isarea = $heimingdan->is_area();
$isip = $heimingdan->is_ip();
$ismobile = $heimingdan->is_mobile();
$isfromepage = $heimingdan->getFromPage(@$referrer);
$data = array(
	'isarea'=>$isarea, 
	'isip'=>$isip, 
	'ismobile'=>$ismobile, 
	'isfromepage'=>$isfromepage,
	'tiaourl'=>'./final.html'
	);

// 生成json格式数据  

$json_string = json_encode((object)$data);
//$obj = json_decode($string); 
echo $json_string;

/**
* 
*/
class Heimingdan
{

	//判断地区，包含返回true
	public function is_area() {
		$arr = array("北京","上海","甘肃");

		$province = $this->getiplocation();
    	$city = $this->getiplocation('city');

	    $isprovince = in_array($province,$arr);
	    $iscity = in_array($city,$arr);
	    $re = ($isprovince || $iscity) ? true : false;
	    
	    return $re;

	}

	//包含ip返回true
	public function is_ip(){
		$arr = array("0.0.0.0");  //ip黑名单

		$ip = $this->getIP();
		$re = in_array($ip, $arr);

		return $re;
	}

	/*判断是否是移动端
	**移动端返回true,pc端返回false
	*/
	function is_mobile(){
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		//return $agent;
		if(strpos($agent, 'iphone') || strpos($agent, 'ipad') || strpos($agent, 'android')){
		 	return true;
		}
		else{
			return false;
		}
	}

	//获取网站来源 直接打开是false,间接打开是true
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


	public function getiplocation($location = 'region') {
		$ip = $this->getIP();
		//$url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip";
		$url = "http://ip.taobao.com/service/getIpInfo.php?ip=$ip";

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //以文件流方式输出

		$a = curl_exec($ch);

		$strjson = json_decode($a);
		$address = $strjson->data->$location;
		
		return $address;
	}


}

