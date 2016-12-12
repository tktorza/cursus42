var drawerIsOpen = false;

function openDrawer()
{
    $('#mainView').animate({width: '50%'}, 300);
    $('#drawer').animate({marginRight: '0px'}, 300);
}

function previewDrawer()
{
    $('#drawer').html('<i class="fa fa-spinner fa-spin loader"></i>');
    $('#mainView').animate({width: '90%'}, 300);
    $('#drawer').animate({marginRight: '-40%'}, 300);
}

function closeDrawer()
{
    $('#mainView').animate({width: '100%'}, 300);
    $('#drawer').animate({marginRight: '-50%'}, 300);
}

function toggleDrawer()
{
    if(drawerIsOpen) {
        closeDrawer();
        drawerIsOpen = false;
    } else {
        openDrawer();
        drawerIsOpen = true;
    }
}

function initFields()
{
    $('.icheck').iCheck({
        checkboxClass: 'icheckbox_square-blue checkbox',
        radioClass: 'iradio_square-blue radio',
    });

    $('.icheck-line').iCheck({
        checkboxClass: 'icheckbox_line-blue checkbox',
        radioClass: 'iradio_line-blue radio',
    });

    $('.datepicker').datetimepicker({
        lang: 'fr',
        format: 'd/m/Y',
        timepicker: false,
        scrollInput: false,
    });

    $('.timepicker').timepicker({ 
        timeFormat: 'HH:mm',
        interval: 5,
        dynamic: false,
    });

    $('input.minicolors').minicolors();

    $('input.mask').each(function() {
        var input = $(this);

        if(input.data('mask') == 'decimal') {
            var mask = {
                alias: 'decimal',
                integerDigits: 5,
                digits: 2,
                digitsOptional: false,
                placeholder: '0',
                allowMinus: false,
                rightAlign: false
            };
        } else {
            var mask = input.data('mask');
        }

        input.inputmask(mask);
    });

    var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));

    elems.forEach(function(html) {
      var switchery = new Switchery(html);
    });

    $('select.select2').select2({width: '100%'});

    $("[data-toggle=tooltip]").tooltip();

    $("[data-toggle=popover]").popover();
}

$(document).on('click', '[data-target=drawer]', function(ev) {
    ev.preventDefault();
    ev.stopPropagation();
    var target = $(this).attr("href");
    previewDrawer();

    $.ajax({
        type: 'GET',
        url: target,
        success: function(response) {
            $('#drawer').html(response);
            openDrawer();
            drawerIsOpen = true;
            initFields();
        }
    });
});

$('.btn-filter a.filter').on('click', function() {
    $(this).closest('.btn-filter').find('.dropdown-toggle').html($(this).html() + '  <span class="caret"></span>');
});

$('.pills-filter li').on('click', function() {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
});

$(document).on('click', 'a[data-target=drawer-close]', function(ev) {
    ev.preventDefault();
    closeDrawer();
    drawerIsOpen = false;
});

$(document).on('submit', '.ajax-form', function(e) {
    e.preventDefault();
    ajaxForm($(this));

    return false;
});

function ajaxForm(form) {
    var callback = form.data('callback');
    var callbackElement = $(callback);
    var ladda = form.find('button[type=submit]').ladda();
    ladda.ladda('start');
    $.ajax({
        type: "POST",
        url: form.attr('action'),
        data: form.serialize(),
        success: function(data) {
            $('#drawer').html(data);
            initFields();
            if(callbackElement) {
                if(callbackElement.data('url')) {
                    $.ajax({
                        type: 'GET',
                        url: callbackElement.data('url'),
                        success: function(data) {
                            callbackElement.html(data);
                            if (typeof ajaxFormCallback == 'function') {
                                ajaxFormCallback();
                            }
                        }
                    });
                }
            }
        },
        error: function(data) {
            $('#drawer').html(data);
            initFields();
        }
    });
}

$(document).on('change', 'form.watch :input', function() {
    $(this).parents('form').data('changed', true);
});

$('form.watch .icheck').on('ifChanged', function (event) {
    $(this).parents('form').data('changed', true);
});

$(document).on('click', '#ajaxFormChangeModal .btn-save', function() {
    $('.ajax-form').each(function() {
        ajaxForm($(this));
    });
    $('#ajaxFormChangeModal').modal('toggle');
});

$(document).on('click', '*[data-target=ajaxModal]', function(ev) {
    ev.preventDefault();
    ev.stopPropagation();

    if($(this).hasClass('btn-form-watch')) {
        if($(this).closest('form').data('changed')) {
            $('#ajaxFormChangeModal').modal('toggle');
            return;
        }
    }

    var target = $(this).attr("href");
    var ladda = null;
    if($(this).hasClass('ladda-button')) {
        ladda = $(this).ladda();
        ladda.ladda('start');
    }

    $("#ajaxModal .modal-content").load(target, function(response, status, xhr) {
        if ( status == "error" )  {
            sweetAlert('Oops...', xhr.responseText, 'error');
        } else {
            $("#ajaxModal").modal("show");
            initFields();
        }

        if(ladda) {
            ladda.ladda('stop');
        }
    });
});

$(document).on('click', '*[data-target=ajaxModal2]', function(ev) {
    ev.preventDefault();
    ev.stopPropagation();

    if($(this).hasClass('btn-form-watch')) {
        if($(this).closest('form').data('changed')) {
            $('#ajaxFormChangeModal').modal('toggle');
            return;
        }
    }

    var target = $(this).attr("href");
    var ladda = null;
    if($(this).hasClass('ladda-button')) {
        ladda = $(this).ladda();
        ladda.ladda('start');
    }

    $("#ajaxModal .modal-content").load(target, function(response, status, xhr) {
        if ( status == "error" )  {
            sweetAlert('Oops...', xhr.responseText, 'error');
        } else {
            $("#ajaxModal").modal("show");
            initFields();
        }

        if(ladda) {
            ladda.ladda('stop');
        }
    });
});

$(document).on('click', 'a.saveForm', function(ev) {
    ev.preventDefault();
    ev.stopPropagation();

    $('#ajaxFormChangeModal').modal('toggle');
});

$('#ajaxModal').on('hidden.bs.modal', function (e) {
    $(this).unbind('click');
});

$(document).on('click', '.confirmModal', function(e){
    e.preventDefault();
    route = $(this).data().route;
    $("#confirmModal").find('form').attr('action', route);
    $("#confirmModal").modal("show");
});

initFields();

$(document).ajaxStart(function() {
    $('#logo').hide();
    $('#spinner').show();
});

$(document).ajaxStop(function() {
    $('#spinner').hide();
    $('#logo').show();
});