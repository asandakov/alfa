<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?

$this->addExternalJS(SITE_TEMPLATE_PATH.'/js/jquery.inputmask.js');

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'alfa');
$jsParams = array(
    'sign' => $signedParams,
    'ajaxUrl' => CUtil::JSEscape($component->getPath() . '/ajax/ajax.php'),
    'ajaxForm' => CUtil::JSEscape($component->getTemplate()->getFolder() . '/form'),
);
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$arFieldsSelect = array(
        0 => array("NAME" => "есть","VALUE" => 1),
        1 => array("NAME" => "нету","VALUE" => 2),
);
?>

<div class="test-block" id="test-block">
    <div class="nm">Сообщение <i title="Добавить" class="fa fa-plus" aria-hidden="true"></i></div>
    <div class="result-err"></div>
    <form  action="<?=$APPLICATION->GetCurDir()?>"  name="filter-form" method="GET">

        <div class="form-row">

            <div class="form-group col-md-3">
                <label for="statusForm">Email</label>
                <select id="statusForm" name="email" class="form-control form-control-lg">
                    <option value="" selected>выберите</option>
                    <? foreach ($arFieldsSelect as $arItem) { ?>
                        <option value="<?=$arItem["VALUE"]?>" <?if ($request["email"] == $arItem["VALUE"]){?> selected<?}?>><?=$arItem["NAME"]?></option>
                    <?}?>
                </select>
            </div>

            <div class="form-group col-md-3">
                <label for="statusForm">Телефон</label>
                <select id="statusForm" name="phone" class="form-control form-control-lg">
                    <option value="" selected>выберите</option>
                    <? foreach ($arFieldsSelect as $arItem) { ?>
                        <option value="<?=$arItem["VALUE"]?>" <?if ($request["phone"] == $arItem["VALUE"]){?> selected<?}?>><?=$arItem["NAME"]?>
                    <?}?>
                </select>
            </div>

            <div class="form-group col-md-3">
                <label for="dateForm">Дата начало</label>
                <input type="text" name="date_start" id="dateForm"
                       onclick="BX.calendar({node: this, field: this, bTime: false});"
                       class="form-control form-control-lg" placeholder="" value="<?=$request["date_start"]?>">
            </div>

            <div class="form-group col-md-3">
                <label for="dateForm">Дата конец</label>
                <input type="text" name="date_end" id="dateForm"
                       onclick="BX.calendar({node: this, field: this, bTime: false});"
                       class="form-control form-control-lg" placeholder="" value="<?=$request["date_end"]?>">
            </div>

        </div>
    </form>


    <table class="info-table">
        <thead>
        <tr>
            <th width="10%">ID</th>
            <th width="10%">Дата</th>
            <th width="50%">Имя</th>
            <th width="15%">Email</th>
            <th width="15%">Телефон</th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($arResult["ITEMS"] as $arItem) { ?>
            <tr data-id="<?= $arItem["ID"] ?>">
                <td><?= $arItem["ID"] ?></td>
                <td><?= $arItem["DATE_CREATE"] ?></td>
                <td><?= $arItem["NAME"] ?></td>
                <td><?= $arItem["PROPERTY_EMAIL_VALUE"] ?></td>
                <td><?= $arItem["PROPERTY_PHONE_VALUE"] ?></td>
            </tr>
        <? } ?>
        </tbody>
    </table>
    <?= $arResult["NAV_STRING"] ?>
</div>


<!-- actionModal -->
<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<script>
    BX.ready(function () {
        AlfaComponent.init(<?=CUtil::PhpToJSObject($jsParams)?>);
    });
</script>



