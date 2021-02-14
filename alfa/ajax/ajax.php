<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//
global $DB,$USER,$APPLICATION;
CBitrixComponent::includeComponentClass("site:alfa");

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
	$params = $signer->unsign($request->get('signedParamsString'), 'alfa');
	
	$params = unserialize(base64_decode($params));
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{	
	die(); // dfd
}
$component = new AlfaSite();
$component->arParams = $component->onPrepareComponentParams($params);
$component->executeComponent();

?>
