jQuery(function ($) {
  var show_modal_wait = false
  $('.fortnox.sync').on('click', function (e) {
    e.preventDefault()
    var button = $(this)
    button.prop('disabled', true)
    button.html('...')

    var orderId = button.data('order-id')
    var data = {
      action: 'fortnox_sync',
      order_id: orderId,
      nonce: fortnox.nonce
    }

    jQuery.post(ajaxurl, data, function (response) {
      button.prop('disabled', false)
      button.html('Synced')
      window.location.reload()
    })
  })

  $('#fortnox_wc_product_pricelist').on('change', function (e) {
    if ($(this).val()) {
      alert(fortnox.wc_product_pricelist)
    }
  })

  $('#fortnox_wc_product_sale_pricelist').on('change', function (e) {
    if ($(this).val()) {
      alert(fortnox.wc_product_sale_pricelist)
    }
  })

  $('#fortnox_process_price').on('change', function (e) {
    if ($(this).val()) {
      alert(fortnox.process_price)
    }
  })

  $('#fortnox_process_sale_price').on('change', function (e) {
    if ($(this).val()) {
      alert(fortnox.process_sale_price)
    }
  })

  $('#fortnox_wc_product_update_stock_level').on('change', function (e) {
    let value = $(this).val();
    if (value == 'yes' || value == 'always') {
      if (!confirm(fortnox.process_wc_stocklevel)) {
        $(this).val(ortnox.default_wc_stocklevel);
      }
    }
  })

  $('#fortnox_process_stocklevel').on('change', function (e) {
    if ($(this).is(':checked')) {
      if (!confirm(fortnox.process_stocklevel)) {
        $(this).prop('checked', false);
      }
    }
  })

  $('#fortnox_remove_vat_from_prices').on('change', function (e) {
    if (!confirm(fortnox.remove_vat_from_prices)) {
      if ($(this).is(':checked')) {
        $(this).prop('checked', false);
      } else {
        $(this).prop('checked', true);
      }
    }
  })

  $('.fortnox-close').on('click', function (e) {
    var modal = document.getElementById('fortnox-modal-id')
    if (modal) { modal.style.display = 'none' }
    show_modal_wait = false
  })

  $('.fortnox.update_product').on('click', function (e) {
    e.preventDefault()
    var button = $(this)
    button.prop('disabled', true)
    button.html('...')

    var product_id = button.data('product-id');
    var action = button.attr("name")
    var data = {
      action: action,
      product_id: product_id,
      nonce: fortnox.nonce
    }

    jQuery.post(ajaxurl, data, function (response) {
      button.prop('disabled', false)
      button.html(fortnox.update_product_text)
      window.location.reload()
    })
  })

  $('.fortnox.update_variation').on('click', function (e) {
    e.preventDefault()
    var button = $(this)
    button.prop('disabled', true)
    button.html('...')

    var product_id = button.data('product-id');
    var action = button.attr("name")
    var data = {
      action: action,
      product_id: product_id,
      nonce: fortnox.nonce
    }

    jQuery.post(ajaxurl, data, function (response) {
      button.prop('disabled', false)
      button.html(fortnox.update_product_text)
    })
  })


  $('#variable_product_options').on('click', '.fortnox.update_variation', function (e) {
    e.stopImmediatePropagation();
    var button = $(this)
    button.prop('disabled', true)
    button.html('...')

    var product_id = button.data('product-id');
    var action = button.attr("name")
    var data = {
      action: action,
      product_id: product_id,
      nonce: fortnox.nonce
    }

    jQuery.post(ajaxurl, data, function (response) {
      button.prop('disabled', false)
      button.html(fortnox.update_variation_text);
    })
  })

  $('.fortnox_connection').on('click', function (e) {
    e.preventDefault()
    var user_email = $('#fortnox_user_email')
    $.post(ajaxurl, { action: 'fortnox_connection', nonce: fortnox.nonce, user_email: user_email.val(), id: e.target.id, email: user_email.val() }, function (response) {
      console.log('fortnox_connect' == e.target.id);
      console.log(response.result == 'success');
      console.log(response);
      if('fortnox_connect' == e.target.id && response.result == 'success'){
        if (response.state == 'mismatch') {
          alert(fortnox.email_warning)
        } else {
          if (confirm(fortnox.redirect_warning)) {
            window.location.replace(response.state)
          }
        } 
      }else if (response.result == 'success') {
        var message = $('<div id="message" class="updated"><p>' + response.message + '</p></div>')
        message.hide()
        message.insertBefore($('#fortnox_titledesc_connect'))
        message.fadeIn('fast', function () {
          setTimeout(function () {
            message.fadeOut('fast', function () {
              message.remove()
              window.location.reload()
            })
          }, 2000)
        })
      } else if (response.result == 'error') {
        alert(response.message)
      }
      
    })
  })

  if ($('.fortnox_wc_products').length && $('.fortnox_wc_products').hasClass('notice-info')) {
    var id = $('.fortnox_wc_products').attr('id');
    var wc_products_timer = setInterval(function () {
      jQuery.post(ajaxurl, { action: 'fortnox_wc_product_message', id: id, nonce: fortnox.nonce }, function (response) {
        if ('success' == response.status || 'false' == response.status) {
          clearInterval(wc_products_timer)
        }
        var inner = document.getElementById(id).innerHTML;
        var repl = inner.replace(/<p>.*<\/p>/, response.message);
        document.getElementById(id).innerHTML = repl

      })
    }, 1000)
  }

  if ($('.fortnox_price_stocklevel').length && $('.fortnox_price_stocklevel').hasClass('notice-info')) {
    var id = $('.fortnox_price_stocklevel').attr('id')
    var price_stocklevel_timer = setInterval(function () {
      jQuery.post(ajaxurl, { action: 'fortnox_price_stocklevel_message', id: id, nonce: fortnox.nonce }, function (response) {
        if ('success' == response.status || 'false' == response.status) {
          clearInterval(price_stocklevel_timer)
        }
        var inner = document.getElementById(id).innerHTML;
        var repl = inner.replace(/<p>.*<\/p>/, response.message);
        document.getElementById(id).innerHTML = repl
      })
    }, 1000)
  }

  $('#fortnox_sync_all').on('click', function (e) {
    e.preventDefault()
    jQuery.post(ajaxurl, { action: 'fortnox_sync_all', nonce: fortnox.nonce }, function (response) {
      window.location.reload()
    })
  })

  $('#fortnox_check_invoices').on('click', function (e) {
    e.preventDefault()
    var sync_days = prompt(fortnox.sync_message, '1')
    if (sync_days != null && sync_days != '' && !isNaN(sync_days)) {
      jQuery.post(ajaxurl, { action: 'fortnox_check_invoices', sync_days: sync_days, nonce: fortnox.nonce }, function (response) {
        window.location.reload()
      })
    } else if (sync_days != null && isNaN(sync_days)) {
      alert(fortnox.sync_warning)
    }
  })

  $('.fortnox_sync_payouts').on('click', function (e) {
    e.preventDefault(e);
    let sync_days = prompt(fortnox.sync_message, '1')
    let id = $(this).attr("id");
    if (sync_days != null && sync_days != '') {
      jQuery.post(ajaxurl, { action: id, sync_days: sync_days, nonce: fortnox.nonce }, function (response) {
        if (response.result == 'success' || response.result == 'error') {
          var message = jQuery('<div id="message" class="updated"><p>' + response.message + '</p></div>')
          message.hide()
          message.insertBefore(jQuery('#fortnox_titledesc_sync_payouts'))
          message.fadeIn('fast', function () {
            setTimeout(function () {
              message.fadeOut('fast', function () {
                message.remove()
              })
            }, 10000)
          })
        }
      })
    } else if (sync_days != null && isNaN(sync_days)) {
      alert(fortnox.sync_warning)
    }
  })

  $('#fortnox_sync_paypal').on('click', function (e) {
    e.preventDefault()
    var sync_days = prompt(fortnox.sync_message, '1')
    if (sync_days != null && sync_days != '' && !isNaN(sync_days)) {
      jQuery.post(ajaxurl, { action: 'fortnox_sync_paypal', sync_days: sync_days, nonce: fortnox.nonce }, function (response) {
        if (response.result == 'success' || response.result == 'error') {
          var message = jQuery('<div id="message" class="updated"><p>' + response.message + '</p></div>')
          message.hide()
          message.insertBefore(jQuery('#fortnox_titledesc_sync_paypal'))
          message.fadeIn('fast', function () {
            setTimeout(function () {
              message.fadeOut('fast', function () {
                message.remove()
              })
            }, 10000)
          })
        }
      })
    } else if (sync_days != null && isNaN(sync_days)) {
      alert(fortnox.sync_warning)
    }
  })

  $('#fortnox_sync_izettle').on('click', function (e) {
    e.preventDefault()
    var sync_days = prompt(fortnox.sync_message, '1')
    if (sync_days != null && sync_days != '') {
      jQuery.post(ajaxurl, { action: 'fortnox_sync_izettle', sync_days: sync_days, nonce: fortnox.nonce }, function (response) {
        if (response.result == 'success' || response.result == 'error') {
          var message = jQuery('<div id="message" class="updated"><p>' + response.message + '</p></div>')
          message.hide()
          message.insertBefore(jQuery('#fortnox_titledesc_sync_izettle'))
          message.fadeIn('fast', function () {
            setTimeout(function () {
              message.fadeOut('fast', function () {
                message.remove()
              })
            }, 10000)
          })
        }
      })
    } else if (sync_days != null && isNaN(sync_days)) {
      alert(fortnox.sync_warning)
    }
  })

  $('#fortnox_clear_cache').on('click', function (e) {
    e.preventDefault()
    jQuery.post(ajaxurl, { action: 'fortnox_clear_cache', nonce: fortnox.nonce }, function (response) {
      if (response.result == 'success' || response.result == 'error') {
        var message = jQuery('<div id="message" class="updated"><p>' + response.message + '</p></div>')
        message.hide()
        message.insertBefore(jQuery('#fortnox_titledesc_clear_cache'))
        message.fadeIn('fast', function () {
          setTimeout(function () {
            message.fadeOut('fast', function () {
              message.remove()
            })
          }, 10000)
        })
      }
    })
  })

  $('.notice-dismiss').on('click', function (e) {
    var is_fortnox_notice = jQuery(e.target).parents('div').hasClass('fortnox_notice')
    if (is_fortnox_notice) {
      var id = jQuery(e.target).parent().attr('id');
      jQuery.post(ajaxurl, { action: 'fortnox_clear_notice', nonce: fortnox.nonce, id: id }, function (response) { })
    }
  })

  function waitForConfirmation() {
    if (show_modal_wait) {
      var modal = document.getElementById('fortnox-modal-id')
      if (modal) { modal.style.display = 'block' }

      jQuery.post(ajaxurl, { 'action': 'fortnox_check_activation', 'nonce': fortnox.nonce }, function (response) {
        var message = response.message
        document.getElementById('fortnox-status').innerHTML = message

        if (response.status == 'success') {
          var modal = document.getElementById('fortnox-modal-id')
          if (modal) { modal.style.display = 'none' }
          show_modal_wait = false
          window.location.reload()
          return
        } else if (response.status == 'failure') {
          return
        } else {
          setTimeout(function () { waitForConfirmation() }, 1000)
        }
      })
    }
  }

  var function_name = 'wcfh_processing_button';

  function setButtonText(button_id, response) {
    if (response.button_text) {
      $('#' + button_id).text(`${response.button_text}`);
    }
  }

  function checkQueue(button_id) {
    jQuery.post(ajaxurl, { action: function_name, id: button_id, nonce: fortnox.nonce, task: 'check' }, function (response) {
      setButtonText(button_id, response);
      displayStatus(button_id, response)
      if (!response.ready) {
        setTimeout(function () { checkQueue(button_id); }, 3000);
      } else {
        removeStatus(button_id, response);
        displayMessage(button_id, response);
      }
    });
  }

  function displayStatus(button_id, response) {
    if (response.status_message) {
      var message = jQuery(`.${button_id}_status p`);
      if (0 !== message.length) {
        message.text(response.status_message);
      } else {
        var message = jQuery(`<div id="message" class="updated ${button_id}_status"><p>${response.status_message}</p></div>`);
        message.hide();
        message.insertBefore(jQuery(`#${button_id}_titledesc`));
        message.fadeIn('fast');
      }
    }
  }

  function removeStatus(button_id, response) {
    var message = jQuery(`.${button_id}_status`);
    message.remove()
  }

  function displayMessage(button_id, response) {
    var message = jQuery(`<div id="message" class="updated"><p>${response.message}</p></div>`)
    message.hide()
    message.insertBefore(jQuery(`#${button_id}_titledesc`))
    message.fadeIn('fast', function () {
      setTimeout(function () {
        message.fadeOut('fast', function () {
          message.remove()
        })
      }, 5000)
    })
  }


  $('.' + function_name).on('click', function (e) {
    e.preventDefault()
    var button_id = $(this).attr('id');
    $.post(ajaxurl, { action: function_name, id: button_id, nonce: fortnox.nonce, task: 'start' }, function (response) {
      displayMessage(button_id, response);
      setButtonText(button_id, response);
      if (!response.ready) {
        checkQueue(button_id);
      }
    })
  });

  /*
 * Check if we have a sync ongoing when loading the page
 */

  let processing_status = $('.wcfh_processing_status');
  if (processing_status.length) {
    var button_id = processing_status.attr('name');
    checkQueue(button_id);
  };


})
