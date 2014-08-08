<?php

require_once 'popbill.php';

$LinkID = 'TESTER';
$SecretKey = 'koHkuukeNY+AO52II2m/e23WdSiKCs0UeeHTWYEES0c=';


$PopbillService = new PopbillBase($LinkID,$SecretKey);

$PopbillService->IsTest(true);

echo substr($PopbillService->GetPopbillURL('1231212312','userid','LOGIN'),0,50). ' ...';
echo chr(10);

$result = $PopbillService->GetBalance('1231212312');

if(is_a($result,'PopbillException')) {
	echo $result->__toString();
	exit();
}
else {
	echo $result;
	echo Chr(10);
}
echo chr(10);

$result = $PopbillService->GetBalance('4108600477');

if(is_a($result,'PopbillException')) {
	echo $result->__toString();
	exit();
}
else {
	echo $result;
	echo Chr(10);
}
echo chr(10);

echo $PopbillService->GetPartnerBalance('1231212312');
echo chr(10);

$joinForm = new JoinForm ();

$joinForm->LinkID 		= $LinkID;
$joinForm->CorpNum 		= '1231212312';
$joinForm->CEOName 		= '대표자성명';
$joinForm->CorpName 	= '테스트사업자상호';
$joinForm->Addr			= '테스트사업자주소';
$joinForm->ZipCode		= '사업장우편번호';
$joinForm->BizType		= '업태';
$joinForm->BizClass		= '업종';
$joinForm->ContactName	= '담당자상명';
$joinForm->ContactEmail	= 'tester@test.com';
$joinForm->ContactTEL	= '07075106766';
$joinForm->ID			= 'userid_php';
$joinForm->PWD			= 'thisispassword';

$result = $PopbillService->JoinMember($joinForm);

echo $result->message;
echo chr(10);
?>
