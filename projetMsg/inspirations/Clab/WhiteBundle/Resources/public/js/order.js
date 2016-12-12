  $(function() {
    setAjaxForms();
    setCartActionsListeners();
    setContentActionsListeners('.list-holder .default');

    $('a.back').click(function(){
      parent.history.back();
      return false;
    });
    $('.offers-header').click(function() {
      $('.offers-header').toggleClass('open');
    });
    $('#orderTypeModal').modal();
    $('.btn-order-type').click(function(e) {
      $('#orderTypeModal').modal('toggle');
      postAndUpdate(e)
    });
    $('.btn-order-type-takeaway').click(function(e) {
      postAndUpdate(e)
    });
    setDeliveryAddressForm();
  });

  function setDeliveryAddressForm() {
    $('#deliveryAddressForm').submit(function(e) {

      $('a.close').click();
      e.preventDefault();
      var formElement = $('#deliveryAddressForm');
      var address = formElement.find('input').val();
      var url = formElement.attr('action');
      setLoader(true);

      $.ajax({
        type: "POST",
        url: url,
        data: formElement.serialize(),
        // weird as shit, goes in error although status 200
        error: function(response) {
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
    $(selector + ' .element-item').click(function(e) {
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
    $('.remove-product').click(postAndUpdate);
    $('.range-handeler').click(postAndUpdate);
    $('[data-toggle="tooltip"]').tooltip();
    $('#coupon-remove').click(postAndUpdate);
    $('#form-add-coupon').unbind('submit');
    $('#form-add-coupon').submit(function (e) {

        var code = $('[name="code"]').val();
        var url = $('#form-add-coupon').attr('action');

        e.preventDefault();
        $.ajax({
            type:     "POST",
            url:      url,
            data:     { code: code },
            dataType: "json",
            // weird as shit, goes in error although status 200
            error: function(response) {
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
    if (!url) return;
    setLoader(true);
    $.ajax({ url: url, type: 'GET' }).done(handleResponse);
  }

  function postAndUpdate(e) {
    var url = e.target.attributes.url ? e.target.attributes.url.value : null;
    if (!url) return;
    setLoader(true);
    $.ajax({
      url: url,
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

  function countCartProduct() {
    if ($('.count').length) {
      var number = 0;

      $('.cart-tooltip').each(function () {
        number += parseInt($(this).find(':selected').val());
      });

      if (!number) {
        $('.count').hide();
      } else {
        $('.count').fadeIn();
      }

      $('.count').html(number);
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
      $('.modal').modal('hide');
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
      countCartProduct();

    // returned a meal step
    } else if (res.hasClass('meal-compose')) {
      $('.list-holder .default').hide();
      $('.list-holder .meal-menu').remove();
      $('.list-holder').prepend('<div class="list-holder meal-menu">' + response + '</div>');
      $('.cancel').click(function(e) {
        var button = $(e.target).closest('button');
        var url = button.attr('url');
        postAndUpdateToUrl(url);
      });
      $('#types-nav').hide();
      activateFormulaNav();
      setAjaxForms();
      setContentActionsListeners('.list-holder .meal-menu');
      $("html, body").animate({ scrollTop: "0px" });
    // returned order type
    } else if (res.hasClass('orderTypeWrapper')) {
      $('.orderTypeWrapper').html(response);
      $('.btn-order-type-takeaway').click(function(e) {
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
      if (!url) return;

      $.ajax({
        type: 'POST',
        url: url,
        data: formElement.serialize()
      }).done(function(response) {
        var styles = $('#cart').attr('style');
        $('#cart').attr('style', styles);

        $('#main .modal.in').modal('hide');
        // wait for the animation to complete
        setTimeout(function() {
          handleResponse(response);
          countCartProduct();
        }, 450); 
      });
    }

  }
