class MagicMessage{
    constructor(type, title, message, okFunction, cancelFunction, name){
        this.class = '';
        this.color = '';
        this.icon = '';

        this.type = type;
        this.title = title;
        this.message = message;
        this.okFunction = okFunction;
        this.cancelFunction = cancelFunction;
        this.name = name;

        if(objectIsSet(name)){
            this.construct();
        }else{
            name = this.getName();
            window[name] = new MagicMessage(type, title, message, okFunction, cancelFunction, name);
        }
    }

    construct() {
        Modals.push(this.name);

        this.setConfig();

        $( "body" ).append("\
            <div class = 'modal type-" + this.class + this.name + "' data-backdrop='static' style='display: none;'>\
                <div class='modal-dialog'>\
                    <div class='modal-content'>" +
            this.htmlHead() +
            this.htmlBody() +
            this.htmlFooter() + "\
                   </div>\
                </div>\
            </div>"
        ).addClass('modal-open');

        $('.' + this.name).find('.content-message').html(this.message);
        $('.' + this.name).fadeIn(300);

        setTimeout(this.addListener(), 0);
    }

    setConfig() {
        var config = {
            confirm: {class: "warning ", color: "orange", icon: "glyphicon-question-sign ti-help-alt"},
            alert: {class: "info ", color: "#00CED1", icon: "glyphicon-info-sign ti-info-alt"},
            error: {class: "warning ", color: "#ff4f3a", icon: "glyphicon-warning-sign ti-alert"},
            default: {class: "default ", color: "#00CED1", icon: "glyphicon-comment ti-alert"}
        };

        this.class = config[this.type]['class'];
        this.color = config[this.type]['color'];
        this.icon = config[this.type]['icon'];
    }

    htmlHead(){
        return "\
            <div class='modal-header' style='background-color: " + this.color + "; padding-top: 10px;'>\
                <div style='text-align: left; padding-left: 0'>\
                    <a class='title card-title' style='font-size: 22px; color: black'><strong>" + this.title + "</strong></a>\
                </div>\
            </div>\
        ";
    }

    htmlBody(){
        return "\
            <div class='modal-body'>\
                <div class = 'content-message' style='font-size: 16px'/>\
            </div>\
        ";
    }

    htmlFooter(){
        return "\
            <div class='modal-footer' style='background-color: whitesmoke;'>\
                <div magic-message = '" + this.name  + "' class='pull-right float-right'>" +
            this.okButton() + this.cancelButton() + "\
                </div>\
            </div>\
        ";
    }

    okButton(){
        return "<div class='btn-group'><button class = 'magic-message-run btn btn-" + (this.type == 'confirm' ? 'success' : 'warning') + "'>" + MagicsoftLanguage('Ok') + "</button></div>";
    }

    cancelButton(){
        return this.type == 'confirm' ? "<div class='btn-group'><a class='magic-message-close btn btn-warning' style='margin: 5px;'>" + MagicsoftLanguage('Cancel') + "</a></div>" : '';
    }

    runOkFunction() {
        setTimeout(this.okFunction, 0);
    }

    runCancelFunction() {
        setTimeout(this.cancelFunction, 0);
    }

    close() {
        Modals.pop();
        
        var divModal = $(document).find('.' + this.name);
        
        setTimeout(function(){
    		divModal.fadeOut(200);
    		setTimeout(function(){
    			divModal.remove();
    			//$('div#' + self.id + '-backdrop').remove();
    			if(Modals.length === 0) $( 'body' ).removeClass('modal-open');
    		}, 0)
    	}, 200);
        

        if(Modals.length === 0) $('body').removeClass('modal-open');

        $(document).find('.' + this.name).fadeOut(200);
    }

    getName(){
        var name = "", textBase = "abcdefghijklmnopqrstuvwxyz123456789";
        for( var i=0; i < 20; i++ ) name += textBase.charAt(Math.floor(Math.random() * textBase.length));
        return 'MSM_' + name;
    }

    addListener(){
        let self = this;

        document.querySelector('.magic-message-run').addEventListener('click', function(event){
            self.runOkFunction();
            self.close();
        })

        document.querySelector('.magic-message-close').addEventListener('click', function(event){
            self.close();
        })
    }
};

MagicMessageDelete = function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).blur();

    var target = '';
    var url = $(this).attr('href');
    var title = MagicsoftLanguage('Delete record');
    var message = MagicsoftLanguage('Are you sure to delete this record?');

    ConfirmMagicMessage(target, url, title, message);
};

var items = document.getElementsByClassName('magic-delete-row');
for (var i = 0; i < items.length; i++) {
    items[i].addEventListener('click', MagicMessageDelete);
};

MagicMessageConfirm = function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).blur();

    var target = $(this).attr('target');
    var url = $(this).attr('href') + ((target !== null && target !== undefined) ? '&target=' + target: '');
    var title = $(this).attr('confirm-title');
    var message = $(this).attr('confirm-text');

    ConfirmMagicMessage(target, url, title, message);
};
var items = document.getElementsByClassName('confirm-message');
for (var i = 0; i < items.length; i++) {
    items[i].addEventListener('click', MagicMessageConfirm);
};


function ConfirmMagicMessage(target, url, title, message){
    new MagicMessage(
        'confirm',
        title,
        message,
        function() {
            if(target === '_blank') {
                openInNewTab(url);
            }else{
                loading(true);
                $.getJSON(url).done(
                    function (data, textStatus, jqXHR) {
                        loading(false);
                        if (data.error) {
                            new MagicMessage(
                                'success',
                                data.data.title,
                                data.data.data,
                                '',
                                function () {
                                    loading(true);
                                    location.reload();
                                }
                            );
                        } else {
                            if (data.redirect) location.reload();
                        }
                    }
                ).fail(
                    function( jqXHR, textStatus, errorThrown ) {
                        loading(false);
                        if (jqXHR.status !== 302) {
                            new MagicMessage(
                                'error',
                                MagicsoftLanguage('Application not completed'),
                                "<strong>Error " + jqXHR.status + "</strong>: " + jqXHR.responseText
                            );
                        }
                    }
                );
            }
        }
    );
}

var items = document.getElementsByClassName('magic-message');
for (var i = 0; i < items.length; i++) {
    items[i].addEventListener('click', MagicMessageConfirm);
};

MagicMessageDefault = function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).blur();

    var title = $(this).attr('message-title');
    var message = $(this).attr('message-text');
    var okFunction = $(this).attr('okFunction');

    new MagicMessage(
        'confirm',
        title,
        message,
        function() {
            setTimeout(okFunction,0);
        }
    );
};