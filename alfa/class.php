<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use Bitrix\Main\Web\Json;
use \Bitrix\Main\Localization\Loc as Loc;


class AlfaSite extends CBitrixComponent
{

    // ПРОВЕРКА НА РАБОТОСПОСОБНОСТЬ
    public function checkComponent()
    {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException(Loc::getMessage('IB_ALFA_SITE'));

        if ($this->arParams["IBLOCK_ID"] <= 0)
            throw new Main\LoaderException(Loc::getMessage('NO_IBLOCK_ID_ALFA_SITE'));

    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams["IBLOCK_ID"] = (int)$arParams["IBLOCK_ID"];

        $arParams["NEWS_COUNT"] = (int)$arParams["NEWS_COUNT"];
        if ($arParams["NEWS_COUNT"] <= 0)
            $arParams["NEWS_COUNT"] = 20;


        if (!isset($arParams["CACHE_TIME"]))
            $arParams["CACHE_TIME"] = 3600;

        return $arParams;
    }

    //ВЫЗОВ МЕТОДА
    protected function doAction($action)
    {
        if (is_callable(array($this, $action))) {
            call_user_func(
                array($this, $action)
            );
        }
    }


    //КАКОЙ МЕТОД
    protected function prepareAction()
    {
        $action = $this->request->get('action');

        if (empty($action)) {
            $action = 'showPage';
        }

        return $action;
    }

    //ДОБАВИТЬ
    private function addFeedback($arData = array())
    {
        $arResult = array("ID" => 0, "ERROR" => "");

        $el = new CIBlockElement;
        $arRealData = array(
            "IBLOCK_ID" => $this->arParams["IBLOCK_ID"]
        );
        if (count($arData) > 0) $arRealData = array_merge($arRealData, $arData);

        if ($idElement = $el->Add($arRealData)) {
            $arResult["ID"] = $idElement;
        } else {
            $arResult["ERROR"] = 'ERROR ADD FEEDBACK' . $el->LAST_ERROR;
        }

        return $arResult;
    }


    //ФОРМА
    public function sendFormAjax()
    {
        global $APPLICATION;

        $arResult = array("MESSAGE" => "", "ERROR" => "");

        parse_str($this->request->get('data'), $arParams);

        switch ($arParams["type"]) {
            case "feedback":
                $arData = array(
                    "NAME" => trim($arParams["name"]),
                    "PROPERTY_VALUES" => array(
                        "PHONE" => preg_replace('/[^0-9]/', '',trim($arParams["phone"])),
                        "EMAIL" => trim($arParams["email"])
                    ),
                );
                $arForm = $this->addFeedback($arData);
                $msg = "Сообщение добавлен успешно";
            break;
        }

        if ($arForm["ID"] > 0) {
            $arResult["MESSAGE"] = $msg;
        } elseif (!empty($arForm["ERROR"])) {
            $arResult["ERROR"] = $arForm["ERROR"];
        } else {
            $arResult["ERROR"] = "Ошибка. Попробуйте еще раз.";
        }

        $result = array(
            'msg' => $arResult["MESSAGE"],
            'error' => $arResult["ERROR"],
            'action' => $this->request->get('action')
        );

        $APPLICATION->RestartBuffer();

        echo Json::encode($result);

        CMain::FinalActions();

        die();
    }


    //ПО УМОЛЧАНИЮ
    protected function showPage()
    {

        CPageOption::SetOptionString("main", "nav_page_in_session", "N");
        $arNavParams = array(
            "nPageSize" => $this->arParams["NEWS_COUNT"],
            "bShowAll" => false
        );
        $arNavigation = \CDBResult::GetNavParams($arNavParams);


        $arRealFilter = array();
        foreach ($_GET as $key => $val) {
            if (in_array($key, array("phone", "email", "date_start", "date_end"))) {
                $val = trim(htmlspecialchars($val));
                if (!empty($val)) {
                    switch ($key) {
                        case 'email':
                            if ($val == 1) $arRealFilter['!=PROPERTY_EMAIL'] = false;
                            if ($val == 2) $arRealFilter['PROPERTY_EMAIL'] = false;
                            break;
                        case 'phone':
                            if ($val == 1) $arRealFilter['!=PROPERTY_PHONE'] = false;
                            if ($val == 2) $arRealFilter['PROPERTY_PHONE'] = false;
                            break;
                        case 'date_start':
                            $arRealFilter['>=DATE_CREATE'] = $val." 00:00:00";
                            break;
                        case 'date_end':
                            $arRealFilter['<=DATE_CREATE'] = $val." 23:59:59";;
                         break;
                    }

                }
            }
        }

        if ($this->startResultCache(false, array($arNavigation,$arRealFilter), $this->getSiteId() . $this->getRelativePath())) {

            $this->arResult["ITEMS"] = array();
            $arFilter = array("IBLOCK_ID" => $this->arParams["IBLOCK_ID"]);
            if (count($arRealFilter) > 0) { $arFilter = array_merge($arFilter,$arRealFilter);}

            $rsElement = \CIBlockElement::GetList(array("ID" => "DESC"), $arFilter, false, $arNavParams, array("ID", "IBLOCK_ID", "NAME","PROPERTY_EMAIL","PROPERTY_PHONE","DATE_CREATE"));
            while ($arFields = $rsElement->GetNext()) {

                $this->arResult["ITEMS"][] = $arFields;
            }

            //дату не будем кешировать для безопасности!!!
            if ((count($this->arResult["ITEMS"]) == 0) || ($arFilter['<=DATE_CREATE'] || $arFilter['>=DATE_CREATE']))  {
                $this->AbortResultCache();
            }

            $this->arResult["NAV_STRING"] = $rsElement->GetPageNavStringEx(
                $navComponentObject,
                "",
                "modern",
                false,
                $this
            );

            $this->setResultCacheKeys(array(
                "NAV_STRING",
            ));

            $this->IncludeComponentTemplate();
        }
    }

    public function executeComponent()
    {
        try {
            $this->checkComponent();
            $action = $this->prepareAction();
            $this->doAction($action);
        } catch (Exception $e) {
            $this->AbortResultCache();
            ShowError($e->getMessage());
        }

    }

}

?>