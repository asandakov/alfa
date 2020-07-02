BX.ready(function () {

});

AlfaComponent = {

    //флаг ajax
    ajaxFlag: true,
    ajaxFormFlag: true,

    //инициализация после загрузки страницы
    init: function (parameters) {
        this.ajaxUrl = parameters.ajaxUrl || '';
        this.ajaxForm = parameters.ajaxForm || '';
        this.signedParamsString = parameters.sign || '';
        this.curPage = window.location.pathname;
        this.blockIdDiv = "test-block";
        this.blockId = BX(this.blockIdDiv);
        this.event();
    },

    //Событие
    event: function () {
        $('#actionModal form input[name=phone]').inputmask('+7 999 999 99 99');

        BX.bind(this.blockId.querySelector('form[name=filter-form] select[name=email]'), 'change', BX.delegate(this.filterAction.bind(this, "FILTER")));
        BX.bind(this.blockId.querySelector('form[name=filter-form] select[name=phone]'), 'change', BX.delegate(this.filterAction.bind(this, "FILTER")));
        BX.bind(this.blockId.querySelector('form[name=filter-form] input[name=date_start]'), 'change', BX.delegate(this.filterAction.bind(this, "FILTER")));
        BX.bind(this.blockId.querySelector('form[name=filter-form] input[name=date_end]'), 'change', BX.delegate(this.filterAction.bind(this, "FILTER")));

        BX.bind(this.blockId.querySelector('.nm .fa-plus'), 'click', BX.delegate(this.formTypeAction.bind(this, "feedback")));
    },


    //показать форму для добваление
    formTypeAction: function (type, event) {
        var target = event.target || event.srcElement;
        this.showFormAction(type, target, {});
    },


    // показать форму определенного типа
    showFormAction: function (type, target, params) {
        if (!type)
            return;

        var postData = {
            'TYPE': type,
            'signedParamsString': this.signedParamsString
        };

        //параметры которые нужно отправить
        if (!!params && typeof params === 'object') {
            for (i in params) {
                if (params.hasOwnProperty(i))
                    postData[i] = params[i];
            }
        }

        //если предыдущий ajax не выполнен то неделаем ничего
        if (!this.ajaxFormFlag) return;
        $(target).addClass("act");
        $("#actionModal .modal-content").html("");
        $("#actionModal .msg").removeClass('err').html("");
        $("#actionModal form[name=form-action]").removeClass('was-validated');
        BX.ajax({
            timeout: 60,
            method: 'POST',
            dataType: 'html',
            url: this.ajaxForm + "/" + type + ".php",
            data: postData,
            onsuccess: BX.delegate(function (result) {
                $("#actionModal .modal-content").html(result);

                switch (type) {
                    case "feedback":
                        $('#actionModal form input[name=phone]').inputmask('+7 999 999 99 99');
                        BX.bind(BX("actionModal").querySelector('form button[name=send]'), 'click', BX.delegate(this.sendFormAction, this));
                    break;
                }

                $("#actionModal").modal('show');
                $(target).removeClass("act");
                this.ajaxFormFlag = true;
            }, this),
            onfailure: BX.delegate(function () {
                this.ajaxFormFlag = true;
                $("#actionModal .modal-content").html("<div class='msg err'>Ошибка. Попробуйте еще раз.</div>");
                $("#actionModal").modal('show');
                $(target).removeClass("act");
            }, this)
        });
    },

    // ПРОВЕРКА ПОЛЕЙ НА ВАЛИДНОСТЬ
    errValide: function () {
        var flag = true;

        $('#actionModal form[name=form-action]').find('.form-control').each(function () {
            if ($(this).prop('required')) {
                if ($(this).val() == '') {
                    flag = false;
                }
            }
        });

        return flag;
    },

    //Отправить форму
    sendFormAction: function (e) {
        e.preventDefault();
        $("#actionModal .msg").removeClass('err').html("");
        $("#actionModal form[name=form-action]").removeClass('was-validated');
        var err = this.errValide();
        if (err) {
            var data = $("#actionModal").find('form[name=form-action]').serialize();
            this.sendRequest({data: data, action: "sendFormAjax"});
        } else {
            $("#actionModal form[name=form-action]").addClass('was-validated');
        }
    },


    //фильтр  в списке - ПОСТРАНИЧНО
    filterAction:function(type)
    {
        $('.test-block  .result-err').html("");
        var filter = $("#test-block").find('form[name=filter-form]').serialize();
        location.href = this.curPage+"?"+filter;
    },

    //общение к серваку
    sendRequest: function (params) {

        var postData,
            i;

        //если предыдущий ajax не выполнен то неделаем ничего
        if (!this.ajaxFlag) return;

        postData = {
            'ajax': 'Y',
            'signedParamsString': this.signedParamsString,
        };


        //параметры которые нужно отправить
        if (!!params && typeof params === 'object') {
            for (i in params) {
                if (params.hasOwnProperty(i))
                    postData[i] = params[i];
            }
        }

        //если нет метода то ничего не делаем
        if (!postData["action"]) return;

        //выполним ajax
        this.ajaxFlag = false;
        BX.ajax({
            timeout: 60,
            method: 'POST',
            dataType: 'json',
            url: this.ajaxUrl,
            data: postData,
            onsuccess: BX.delegate(function (result) {
                this.beforeAction(postData["action"], result);
                this.ajaxFlag = true;
            }, this),
            onfailure: BX.delegate(function () {
                this.beforeErrorAction(postData["action"]);
                this.ajaxFlag = true;
            }, this)
        });

    },
    //после ajax сделаем что то - ОШИБКА
    beforeErrorAction: function (type) {
        switch (type) {
            case "sendFormAjax":
                $("#actionModal .msg").addClass('err').html("Ошибка. Попробуйте еще раз.");
                break;
       
        }
    },


    //после ajax сделаем что то
    beforeAction: function (type, result) {
        switch (type) {
            case "sendFormAjax":
                this.sendFormAjaxBefore(result);
                break;

        }
    },


    //форма: действия над городами
    sendFormAjaxBefore: function (result) {
        if (result["error"]) {
            $("#actionModal .msg").addClass('err').html(result["error"]);
        } else {
            var msg = "Операция удачно выполнена";
            if (result["msg"]) {
                var msg = result["msg"];
            }

            $("#actionModal .msg").html(msg);
            $("#actionModal form").remove();
            setTimeout(BX.delegate(function () {
                $("#actionModal").modal('hide');
                window.location.href = this.curPage;
            }, this), 700);
        }
    },



    //СКРОЛЛ
    scrollTop: function (id_block) {
        $('html, body').animate({scrollTop: $('#' + id_block).offset().top}, 500);
    }

}