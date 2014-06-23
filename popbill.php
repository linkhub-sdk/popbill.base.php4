<?php
/**
* =====================================================================================
* Class for base module for Popbill API SDK. It include base functionality for
* RESTful web service request and parse json result. It uses Linkhub module
* to accomplish authentication APIs.
*
* This module uses curl and openssl for HTTPS Request. So related modules must
* be installed and enabled.
*
* http://www.linkhub.co.kr
* Author : Kim Seongjun (pallet027@gmail.com)
* Written : 2014-04-15
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anythings.
* ======================================================================================
*/

require_once 'Linkhub/linkhub.auth.php';
require_once 'Linkhub/JSON.php';

class PopbillBase
{
	var $Token = NULL;
	
    function PopbillBase($LinkID,$SecretKey) {
    	$this->Linkhub = new Linkhub($LinkID,$SecretKey);
    	$this->scopes[] = 'member';
    	$this->IsTest = false;
    	$this->VERS = '1.0';
    	$this->ServiceID_REAL = 'POPBILL';
    	$this->ServiceID_TEST = 'POPBILL_TEST';
    	$this->ServiceURL_REAL = 'https://popbill.linkhub.co.kr';
    	$this->ServiceURL_TEST = 'https://popbill_test.linkhub.co.kr';
    }
    
    function IsTest($T) {$this->IsTest = $T;}

    function AddScope($scope) {$this->scopes[] = $scope;}
    
    function getsession_Token($CorpNum) {
		    	
    	$Refresh = false;
    	
    	if(is_null($this->Token)) {
    		$Refresh = true;
    	}
    	else {
    		$Expiration = date($this->Token->expiration);
    		$now = date("Y-m-d H:i:s",time());
    		$Refresh = $Expiration < $now; 
    	}
    	
    	if($Refresh) {
    		
    		$_Token = $this->Linkhub->getToken($this->IsTest ? $this->ServiceID_TEST : $this->ServiceID_REAL,$CorpNum, $this->scopes);
    		if(is_a($_Token,'LinkhubException')) {
    			trigger_error($_Token->__toString(),E_USER_ERROR);
    		}
    		$this->Token = $_Token;
    	}
    	
    	return $this->Token->session_token;
    }
 
    //팝빌 연결 URL함수
    function GetPopbillURL($CorpNum ,$UserID, $TOGO) {
    	$response = $this->executeCURL('/?TG='.$TOGO,$CorpNum,$UserID);
    	if(is_a($response ,'PopbillException')) return $response;
    	return $response->url;
    }
 
    //회원가입
    function JoinMember($JoinForm) {
    	$postdata = $this->Linkhub->json_encode($JoinForm);
   		return $this->executeCURL('/Join',null,null,true,null,$postdata);
    	
    }
 
    //회원 잔여포인트 확인
    function GetBalance($CorpNum) {
    	return $this->Linkhub->getBalance($this->getsession_Token($CorpNum),$this->IsTest ? $this->ServiceID_TEST : $this->ServiceID_REAL);
    }
 
    //파트너 잔여포인트 확인
    function GetPartnerBalance($CorpNum) {
    	return $this->Linkhub->getPartnerBalance($this->getsession_Token($CorpNum),$this->IsTest ? $this->ServiceID_TEST : $this->ServiceID_REAL);
    }
    
    function executeCURL($uri,$CorpNum = null,$userID = null,$isPost = false, $action = null, $postdata = null,$isMultiPart=false) {
		$http = curl_init(($this->IsTest ? $this->ServiceURL_TEST : $this->ServiceURL_REAL).$uri);
		$header = array();
		
		if(is_null($CorpNum) == false) {
			$header[] = 'Authorization: Bearer '.$this->getsession_Token($CorpNum);
		}
		if(is_null($userID) == false) {
			$header[] = 'x-pb-userid: '.$userID;
		}
		if(is_null($action) == false) {
			$header[] = 'X-HTTP-Method-Override: '.$action;
		}
		if($isMultiPart == false) {
			$header[] = 'Content-Type: Application/json';
		}
		
		if($isPost) {
			curl_setopt($http, CURLOPT_POST,1);
			curl_setopt($http, CURLOPT_POSTFIELDS, $postdata);   
		}
		curl_setopt($http, CURLOPT_HTTPHEADER,$header);
		curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
		
		$responseJson = curl_exec($http);
		$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
		
		curl_close($http);
			
		if($http_status != 200) {
			return new PopbillException($responseJson);
		}
		
		return $this->Linkhub->json_decode($responseJson);
	}
}

class JoinForm 
{
	var $LinkID;
	var $CorpNum;
	var $CEOName;
	var $CorpName;
	var $Addr;
	var $ZipCode;
	var $BizType;
	var $BizClass;
	var $ContactName;
	var $ContactEmail;
	var $ContactTEL;
	var $ID;
	var $PWD;
}

class PopbillException
{
	var $code;
	var $message;

	function PopbillException($responseJson) {
		$json = new Services_JSON();
		$result = $json->decode($responseJson);
		$this->code = $result->code;
		$this->message = $result->message;
		$this->isException = true;
		return $this;
	}
	function __toString() {
		return "[code : {$this->code}] : {$this->message}\n";
	}
}
?>