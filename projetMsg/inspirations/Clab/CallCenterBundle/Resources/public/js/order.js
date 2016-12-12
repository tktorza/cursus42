(function (global) {

    $('.option_group :checkbox').change(function () {
        var count_checked = $(this).closest('.option_group').find(':checkbox:checked').length;

        if ($(this).attr('max') && count_checked >= $(this).attr('max')) {
            $(this).closest('.option_group').find(':checkbox:not(:checked)').attr('disabled', true);
            $(this).closest('.option_group').find(':checkbox:not(:checked)').parent().next('.option_order_label').css('color',
                '#dadada');
        } else {
            $(this).closest('.option_group').find(':checkbox').removeAttr('disabled');
            $(this).closest('.option_group').find(':checkbox:not(:checked)').parent().next('.option_order_label').css('color',
                '#333333');
        }

        if (count_checked < $(this).attr('min')) {
            $(this).closest('.option_group').parent().find('.errors_option').html('nombre de choix incorrect').slideDown('slow');
            $(this).closest('.option_group').parent().find('h4').css('color', "#c8584b");
        } else {
            $(this).closest('.option_group').parent().find('.errors_option').slideUp('slow').html('');
            $(this).closest('.option_group').parent().find('h4').css('color', "#333333");
        }

    });

    $('.submit_option').click(function (e) {

        var $check_box_groups = $(this).parent().find('.option_group');

        $check_box_groups.each(function () {

            if ($(this).find('input:required').length > 0) {
                if ($(this).find(':radio:checked').length != 1) {
                    e.preventDefault();
                    console.log($(this).parent().find('.errors_option'));
                    $(this).parent().find('.errors_option').html('obligatoire').slideDown('slow');
                    $(this).parent().find('h4').css('color', "#c8584b");
                } else {
                    $(this).parent().find('.errors_option').slideUp('slow').html('');
                    $(this).parent().find('h4').css('color', "#333333");
                }
            } else {

                var count_checked = $(this).find(':checkbox:checked').length;
                var min_checked = $(this).find(':checkbox').eq(0).attr('min');
                if (min_checked > 0 && count_checked < min_checked) {
                    e.preventDefault();
                    $(this).parent().find('.errors_option').html('nombre de choix incorrect').slideDown('slow');
                    $(this).parent().find('h4').css('color', "#c8584b");
                } else {
                    $(this).parent().find('.errors_option').slideUp('slow').html('');
                    $(this).parent().find('h4').css('color', "#333333");
                }
            }

        });

    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('.errors_option').slideUp('slow').html('');
        $(this).find('h4').css('color', "#333333");

        $(this).find(':radio:checked').prop('checked',
            false).parent().removeClass('jcf-checked').addClass('jcf-unchecked').parent().removeClass();

        $(this).find(':checkbox:checked').prop('checked',
            false).parent().removeClass('jcf-checked').addClass('jcf-unchecked').parent().removeClass();

    });


    $(function () {
        setAjaxForms();
        setCartActionsListeners();
        setContentActionsListeners('.list-holder .default');
        // $('#search-bar').hide();
        if ($(window).width() > 810) {
            $('body').scrollspy({target: '#types-nav-anchor', offset: 50})
        } else {
            $('body').scrollspy({target: '#types-nav-anchor', offset: 60})
        }
        $('a.back').click(function () {
            parent.history.back();
            return false;
        });
        $('.offers-header').click(function () {
            $('.offers-header').toggleClass('open');
        });
        $('#orderTypeModal').modal();
        $('.btn-order-type').click(function (e) {
            $('#orderTypeModal').modal('toggle');
            postAndUpdate(e)
        });
        $('.btn-order-type-takeaway').click(function (e) {
            postAndUpdate(e)
        });
        setDeliveryAddressForm();
    });

    function setDeliveryAddressForm() {
        $('#deliveryAddressForm').submit(function (e) {

            $('a.close').click();
            e.preventDefault();
            var formElement = $('#deliveryAddressForm');
            var address = formElement.find('input').val();
            var url = formElement.attr('action');
            setLoader(true);

            $.ajax({
                type:     "POST",
                url:      url,
                data:     formElement.serialize(),
                // weird as shit, goes in error although status 200
                error:    function (response) {
                    if (response.status === 200) {
                        handleResponse(response.responseText);
                        $('a[href=#deliveryAddressModal] .text').text(address);
                        setLoader(false);
                    }
                },
                dataType: "json"
            });
        });
    }


    function setContentActionsListeners(selector) {
        $(selector + ' .element-item').click(function (e) {
            if (!e.target.href && !$(e.target).hasClass('modal')) {
                var element = $(e.target).closest('.element-item');
                var link = element.find('a.add-list');
                if (link.length > 0) {
                    var modalRef = link[0].href;
                    var href = modalRef.slice(modalRef.indexOf('#'), modalRef.length);
                    $(href).modal();
                    return;
                }
                var url = element.attr('url');
                postAndUpdateToUrl(url)
            }
        });
    }

    function setCartActionsListeners() {
        // $('#empty-cart').click(postAndUpdate);
        $('.remove-product').click(postAndUpdate);
        $('.range-handeler').click(postAndUpdate);
        $('#coupon-remove').click(postAndUpdate);
        $('#form-add-coupon').unbind('submit');
        $('#form-add-coupon').submit(function (e) {

            var code = $('[name="code"]').val();
            var url = $('#form-add-coupon').attr('action');

            e.preventDefault();
            $.ajax({
                type:     "POST",
                url:      url,
                data:     {code: code},
                dataType: "json",
                // weird as shit, goes in error although status 200
                error:    function (response) {
                    if (response.status !== 200) {
                        swal("Coupon", "Ce coupon n'existe pas", "error");
                    } else {
                        handleResponse(response.responseText);
                    }
                }
            });
        });
    }

    function activateFormulaNav() {
        $('.formula-list button').click(postAndUpdate);
    }

    function postAndUpdateToUrl(url) {
      if (!url) {
        return;
      }
        setLoader(true);
        $.ajax({url: url, type: 'GET'}).done(handleResponse);
    }

    function postAndUpdate(e) {
        var url = e.target.attributes.url? e.target.attributes.url.value: null;
      if (!url) {
        return;
      }
        setLoader(true);
        $.ajax({
            url:  url,
            type: 'GET'
        }).done(handleResponse);
    }

    function setLoader(on) {
        var loader = $('.cart-complete .loader');
        if (on) {
            loader.show();
        } else {
            loader.hide();
        }
    }

    function handleResponse(response) {
        var res = $(response);
        setLoader(false);

        // returned a cart
        if (res.hasClass('cart-complete')) {

            var oldLength = $('.products-wrapper .cart-product').length;
            var oldOffset = $('.products-wrapper').scrollTop();

            $('.list-holder .meal-menu').remove();
            $('.list-holder .default').show();
            $('.cart-wrapper').html(response);
            $('#types-nav').show();

            var products = $('.products-wrapper .cart-product');
            if (products.length > 0) {
                if (products.length > oldLength) {
                    var scroll = window.scrollY;
                    products.last()[0].scrollIntoView();
                    window.scrollTo(0, scroll);
                } else {
                    $('.products-wrapper').scrollTop(oldOffset);
                }
            }
            setAjaxForms();
            setCartActionsListeners();

            // returned a meal step
        } else if (res.hasClass('meal-compose')) {
            $('.list-holder .default').hide();
            $('.list-holder .meal-menu').remove();
            $('.list-holder').prepend('<div class="list-holder meal-menu">' + response + '</div>');
            $('.cancel').click(function (e) {
                var button = $(e.target).closest('button');
                var url = button.attr('url');
                postAndUpdateToUrl(url);
            });
            $('#types-nav').hide();
            activateFormulaNav();
            setAjaxForms();
            setContentActionsListeners('.list-holder .meal-menu');

            // returned order type
        } else if (res.hasClass('orderTypeWrapper')) {
            $('.orderTypeWrapper').html(response);
            $('.btn-order-type-takeaway').click(function (e) {
                postAndUpdate(e)
            });
        } else {
            $('.list-holder .meal-menu').remove();
            $('.list-holder .default').show();
            $('#types-nav').show();
            setAjaxForms();
        }
    }

    function setAjaxForms() {

        $('.options-form-container form').unbind('submit');
        $('.options-form-container form').submit(submitOptionForm);

        function submitOptionForm(e) {
            e.preventDefault();
            var id = e.target.attributes.name.value;
            var container = $('.options-form-container[data-id="' + id + '"]');
            var formElement = container.find('form').first();
            var url = formElement.attr("action");
          if (!url) {
            return;
          }

            $.ajax({
                type: 'POST',
                url:  url,
                data: formElement.serialize()
            }).done(function (response) {
                console.log(response);
                $('#main .modal.in').modal('hide');
                // wait for the animation to complete
                setTimeout(function () {
                    handleResponse(response);
                }, 450);
            });
        }
    }

})(window);
