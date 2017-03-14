window.loginVM = new Vue(
    {
        el: '.login-form',
        data: {
            loading: false,
            success: false,
            btnSuccess: false,
            btnError: false,
            btnInterval: null,
        },
        methods: {
            postData: function() {
                var vm = this;
                var url = vm.$el.action || '';
                var method = vm.$el.method || 'post';

                if (vm.loading)
                    return;

                vm.loading = true;

                var config = {
                    url: url,
                    method: method,
                    data: $(vm.$el).serialize(),
                };

                $.ajax(config)
                 .done(function() {
                     vm.btnSuccess = true;
                     Materialize.toast('You\'ve been logged in!', 1500, 'status success');
                     window.location.reload();
                 })
                 .fail(function(d) {
                     var data = d.responseJSON || { errors: [ 'Something went wrong!' ] };
                     var messages = data.errors;

                     vm.btnError = true;

                     for (var message in messages) {
                         if (messages.hasOwnProperty(message))
                             Materialize.toast(messages[ message ], 1500, 'status error');
                     }
                 })
                 .always(function() {
                     vm.loading = false;
                     window.clearInterval(vm.btnInterval);
                     vm.btnInterval = window.setTimeout(function() {
                         vm.btnSuccess = false;
                         vm.btnError = false;
                     }, 1500);
                 });
            },
        },
    });
