define(
  ['jquery', 'translations/messages'], function($){
    var clientForm = {};

    var delay = 2000;

    var showHandlingResult = function (type, msg) {
      $('#deletion_result').addClass('alert-'+type).text(msg).removeClass('hide');
    };

    var clearMessages = function () {
      $('#deletion_result').addClass('hide').text('');
    };

    var showLoading = function () {
      $('#deletion_loading').removeClass('hide');
    };

    var hideLoading = function () {
      $('#deletion_loading').addClass('hide');
    };

    var redirectToCustomerList = function () {
      var url = Routing.generate('sam_customer_list');
      window.location.replace(url);
    };

    var removeClientById = function (clientId) {
      $.ajax({
        url: Routing.generate('sam_customer_delete', { id: clientId }),
        dataType: 'json',
        type: 'DELETE',
        beforeSend: showLoading
      })
      .done(function(result) {
        showHandlingResult('success', result.message);
        setTimeout(redirectToCustomerList, delay);
      })
      .fail(function (jqXHR) {
        showHandlingResult('danger', jqXHR.responseJSON.message);
      })
      .always(function() {
        hideLoading();
        setTimeout(function() {
            clearMessages();
            $('#modal-delete-client').modal('hide');
          },
          delay
        );
      });
    }

    clientForm.handleDelete = function () {
      $('#delete_client').click(function (event) {
        event.preventDefault();
        var clientId = $(this).data('customer-id');
        $('#modal-delete-client').modal('show');

        $('#submit-client-deletion').on('click', function (e) {
          e.preventDefault();
          removeClientById(clientId);
        });
      });
    }

    return clientForm;
  }
);
