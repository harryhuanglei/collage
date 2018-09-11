<?php

/**

 * 根据地理坐标获取国家、省份、城市，及周边数据类(利用百度Geocoding API实现)

 * 百度密钥获取方法：http://lbsyun.baidu.com/apiconsole/key?application=key（需要先注册百度开发者账号）

 * Date:    2015-07-30

 * Author:  fdipzone

 * Ver: 1.0

 *

 * Func:

 * Public  getAddressComponent 根据地址获取国家、省份、城市及周边数据

 * Private toCurl              使用curl调用百度Geocoding API

 */



class Geocoding {


    // 百度Geocoding API

    const API = 'http://apis.map.qq.com/ws/place/v1/search?';
	const PLACE_API ='http://apis.map.qq.com/ws/place/v1/search';
	const DISTANCEAPI= 'http://restapi.amap.com/v3/direction/walking?';
	const AddressDescribeAPI ='http://apis.map.qq.com/ws/streetview/v1/image?';
	const BAIDUGONCOV ='http://restapi.amap.com/v3/assistant/coordinate/convert?';
	const BAIDULOCALTION ='http://restapi.amap.com/v3/geocode/regeo?';
	const BAIDUSEARCHADDRESSAPI='http://restapi.amap.com/v3/place/text?';


    // 不显示周边数据

    const NO_POIS = 1;

    // 显示周边数据

    const POIS = 1; 

	public static function getbaidulocationaddress($baiduak,$location)
	{
     	$param = array(
                'key' => $baiduak,
				'location' =>$location,
				'radius'=>'500',
				'output' =>'JSON',
				'roadlevel' =>1,
				'extensions' =>'all'
       	 );
        // 请求百度api
        $response = self::togetCurl(self::BAIDULOCALTION, $param);
        $result = array();
        if($response){
            $result = json_decode($response, true);
        }
        return $result;
	}


	//获取坐标位置描述
	public static function getchangebaidulocaltion($baiduak, $location){
		//$longitude ='108.9491';
		//$latitude = '34.24521';
     $param = array(
                'key' => $baiduak,
				'locations' => $location,
				'coordsys' =>'gps',
				'output' =>'JSON'
        );
        // 请求百度api
        $response = self::togetCurl(self::BAIDUGONCOV, $param);
        $result = array();
        if($response){
            $result = json_decode($response, true);
        }
        return $result;
    }
	




    /**

     * 根据地址获取国家、省份、城市及周边数据

     * @param  String  $ak        腾讯key(密钥)

     * @param  Decimal $longitude 经度

     * @param  Decimal $latitude  纬度

     * @param  Int     $pois      是否显示周边数据

     * @return Array

     */

    public static function getAddressComponent($ak, $longitude, $latitude,$size,$page_size){

		//$longitude ='109.043030';

		//$latitude = '34.286940';

     $param = array(

                'key' => $ak,

                'boundary' => "nearby($latitude,$longitude,1000)",

                'filter' => "category=房产小区,教育学校,基础设施,银行金融,文化场馆,医疗保健,娱乐休闲,生活服务,购物,美食",

				'page_size' => 10,

			//	'keyword'=>'西安',

				'page_index' =>1,

				'orderby' =>"_distance"

        );

        // 请求百度api

        $response = self::togetCurl(self::API, $param);

        $result = array();

        if($response){

            $result = json_decode($response, true);

        }

        return $result;

    }
	//获取坐标位置描述
	public static function getAddressDescribe($ak, $location){
		//$longitude ='109.043030';

		//$latitude = '34.286940';

     $param = array(

                'key' => $ak,
				
				'size' => '600*480',

                'location' =>$location

             
        );
		

        // 请求百度api
        $response = self::togetCurl(self::AddressDescribeAPI, $param);

        $result = array();

        if($response){

            $result = json_decode($response, true);

        }

        return $result;

    }
	
	

	

	



    public static function getsearchaddress($ak, $city, $keywords,$size,$page_size){
		
    	$param = array(
                'key' => $ak,
                'keywords' => $keywords,
				'city_limit' => true,
				'citylimit' =>true,
				'offset' =>20,
				'output' =>'json',
				'city' =>$city
        );
		
	

        // 请求百度api

        $response = self::togetCurl(self::BAIDUSEARCHADDRESSAPI, $param);
        $result = array();
        if($response){
            $result = json_decode($response, true);
        }
        return $result;

    }

	



	  public static function getdistance($ak,$to, $from){
		$param = array(
				'output' => "JSON",
				'origin' => $from,
				'destination' => $to,
				'key' => $ak,
		);
		// 请求百度api

		$response = self::togetCurl(self::DISTANCEAPI, $param);

		$result = array();

		if($response){

			$result = json_decode($response, true);

		}
			

		return $result;

	  }

	







    /**

     * 使用curl调用百度Geocoding API

     * @param  String $url    请求的地址

     * @param  Array  $param  请求的参数

     * @return JSON

     */

		private static function toCurl($url, $param=array()){



        $ch = curl_init();



        if(substr($url,0,5)=='https'){

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在

        }



        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));



        $response = curl_exec($ch);



        if($error=curl_error($ch)){

            return false;

        }



        curl_close($ch);



        return $response;



    }

	

	

	

	  private static function togetCurl($url, $param=array()){



        $ch = curl_init();



        if(substr($url,0,5)=='https'){

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在

        }



        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_URL, $url.http_build_query($param));

        // curl_setopt($ch, CURLOPT_POST, true);

        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));

//echo $url.http_build_query($param);exit;

        $response = curl_exec($ch);



        if($error=curl_error($ch)){

            return false;

        }



        curl_close($ch);



        return $response;



    }





}



?>