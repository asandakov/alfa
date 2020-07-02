<?
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arResult = $_POST;
if ($arResult["TYPE"] == "feedback") {
    ?>
    <div class="modal-header">
        <div class="modal-title" id="actionModalLabel">Добавить<span></span>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="msg"></div>
    <form name="form-action" class="needs-validation" id="form-action" method="GET">
        <input type="hidden" name="type" value="<?=$arResult["TYPE"]?>">

        <div class="modal-body">
            <div class="form-group">
                <label for="nameForm">Имя</label>
                <input type="text" name="name" class="form-control form-control-lg" value=""
                       id="nameForm" required>
                <div class="invalid-feedback">
                    Поле обязательно для заполнения
                </div>
            </div>

            <div class="form-group">
                <label for="nameForm">Телефон</label>
                <input type="text" name="phone" class="form-control form-control-lg" value=""
                       id="nameForm" >
                <div class="invalid-feedback">
                    Поле обязательно для заполнения
                </div>
            </div>

            <div class="form-group">
                <label for="nameForm">Email</label>
                <input type="email" name="email" class="form-control form-control-lg" value=""
                       id="nameForm" >
                <div class="invalid-feedback">
                    Неверный формат Email
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">Отмена</button>
            <button type="button" name="send" class="btn btn-driver btn-lg btn-driver-save-status">Добавить</button>
        </div>
    </form>
<? } ?>