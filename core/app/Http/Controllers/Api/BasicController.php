<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Language;
use Illuminate\Http\Request;

class BasicController extends Controller
{
    public function generalSetting(){
    	$general = GeneralSetting::first();
		$notify[] = 'General setting data';
		return response()->json([
			'code'=>200,
			'status'=>'ok',
	        'message'=>['success'=>$notify],
	        'data'=>['general_setting'=>$general]
	    ]);
    }

    public function unauthenticate(){
    	$notify[] = 'Unauthenticated user';
		return response()->json([
			'code'=>403,
			'status'=>'unauthorized',
	        'message'=>['error'=>$notify]
	    ]);
    }

    public function languages(){
    	$languages = Language::get();
    	return response()->json([
			'code'=>200,
			'status'=>'ok',
	        'data'=>[
	        	'languages'=>$languages,
	        	'image_path'=>imagePath()['language']['path']
	        ]
	    ]);
    }

    public function languageData($code){
    	$language = Language::where('code',$code)->first();
    	if (!$language) {
    		$notify[] = 'Language not found';
    		return response()->json([
				'code'=>404,
				'status'=>'error',
		        'message'=>['error'=>$notify]
		    ]);
    	}
    	$jsonFile = strtolower($language->code) . '.json';
    	$fileData = resource_path('lang/').$jsonFile;
    	$languageData = json_decode(file_get_contents($fileData));
		return response()->json([
			'code'=>200,
			'status'=>'ok',
	        'message'=>[
	        	'language_data'=>$languageData
	        ]
	    ]);
    }
    
    public function gamesInit(Request $request){
      $url = 'https://staging.slotegrator.com/api/index.php/v1/games/init';
      $merchantId = 'ae88ab8ee84ff40a76f1ec2e0f7b5caa';
      $merchantKey = '4953e491031d3f9e7545223885cf43a7403f14cb';
      $nonce = md5(uniqid(mt_rand(), true)); $time = time();

      $headers = ['X-Merchant-Id' => $merchantId, 'X-Timestamp' => $time, 'X-Nonce' => $nonce];

      $requestParams = ['game_uuid' => $request->game_uuid, 'player_id' => $request->player_id, 'currency' => 'EUR', 'player_name' => $request->player_name, 'session_id' => $nonce];

      $mergedParams = array_merge($requestParams, $headers);
      ksort($mergedParams); $hashString = http_build_query($mergedParams);

      $XSign = hash_hmac('sha1', $hashString, $merchantKey);

      ksort($requestParams); $postdata = http_build_query($requestParams);
      
      $postHeader = array('X-Merchant-Id: '.$merchantId, 'X-Timestamp: '.$time, 'X-Nonce: '.$nonce, 'X-Sign: '.$XSign, 'Accept: application/json', 'Enctype: application/x-www-form-urlencoded');

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      /*curl_setopt($ch, CURLOPT_POST, 1);*/
      curl_setopt($ch, CURLOPT_HTTPHEADER, $postHeader);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec($ch); 
      /*if(curl_exec($ch) === false) {
          echo 'Curl error: ' . curl_error($ch);
      } else {
          echo 'Operation completed without any errors';
      }*/
      
      return response()->json([
			'code'=>200,
			'status'=>'ok',
	        'message'=>[
	        	'language_data'=>json_decode($result, true),
            'headers' => $headers,
            'requestParamsHeaders' => $requestParams,
            'hashstring'=> $hashString,
            'postDataHeader'=> $postHeader
	        ]
	    ]);

    }
}
