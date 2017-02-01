var signupVM = new Vue(
    {
        el        : '#invite-user',
        data      : {
            loading       : false,
            btnError      : false,
            btnSuccess    : false,
            password      : '',
            repeatPassword: ''
        },
        methods   : {
            getUrl       : function () {
                return $(this.$el).attr('action');
            },
            getFormData  : function () {
                return $(this.$el).serializeObject();
            },
            getData      : function () {
                var formData = this.getFormData();

                return formData;
            },
            send         : function (nudes) {
                var vm   = this;
                var data = this.getData();
                var url  = this.getUrl();

                vm.loading = true;

                $.post(url, data)
                 .done(function () {
                     Materialize.toast('You successfully signed up!', 1500, 'status success');
                     vm.login(data.data);
                     vm.btnStatus('btnSuccess');
                 })
                 .fail(function (data) {
                     vm.displayErrors(data);
                     vm.btnStatus('btnError');
                 })
                 .always(function () {
                     vm.loading = false;
                 });
            },
            displayErrors: function (data) {
                data  = data.responseJSON || { errors: [ 'Something went wrong!' ] };
                var d = data.errors;

                for (var i in d) {
                    if (!d.hasOwnProperty(i))
                        continue;

                    Materialize.toast(d[ i ], 3000, 'status error');
                }
            },
            getUser      : function () {
                return $(this.$el).attr('data-user');
            },
            getLoginUrl  : function () {
                return $(this.$el).attr('data-login');
            },
            getLessonsUrl: function () {
                return $(this.$el).attr('data-lessons');
            },
            login        : function (data) {
                var vm  = this;
                var cls = 'click-to-log-user-in';
                swal(
                    {
                        title              : 'Success!',
                        html               : 'You\'ve successfully signed up!<br>Now you\'re being logged in...',
                        type               : 'info',
                        confirmButtonText  : 'Log in',
                        showLoaderOnConfirm: true,
                        confirmButtonClass : cls,
                        preConfirm         : function () {
                            return new Promise(function (resolve, reject) {
                                var csrf = getCsrf();
                                var url  = vm.getLoginUrl();

                                var data = {
                                    identifier: vm.getUser(),
                                    password  : vm.password
                                };

                                data[ csrf.key ] = csrf.value;

                                $.post(url, data)
                                 .done(function () {
                                     resolve();
                                 })
                                 .fail(function (data) {
                                     vm.displayErrors(data);
                                     reject('Things went amiss');
                                 });
                            })
                        },
                        allowOutsideClick  : false,
                        onOpen             : function () {
                            setTimeout(function () {
                                $('.' + cls).click();
                            }, 50);
                        }
                    }
                ).then(function () {
                    swal(
                        {
                            type   : 'success',
                            title  : 'You\'re ready to go',
                            html   : 'Now you just need to select lessons you\'re gonna be attending',
                            onClose: function () {
                                window.location.href = vm.getLessonsUrl();
                            }
                        }
                    )
                });
            },
            btnStatus    : function (status, cb) {
                var vm = this;

                this[ status ] = true;

                var fn = function () {
                    vm[ status ] = false;

                    if (!isFunction(cb))
                        cb = new Function();

                    cb(status);
                };

                return window.setTimeout(fn, 1500);
            }
        },
        computed  : {
            passwordsMatch: function () {
                if (!(this.password && this.repeatPassword))
                    return true;

                return this.password == this.repeatPassword;
            },
            submittable   : function () {
                return this.password && this.repeatPassword && this.passwordsMatch;
            }
        },
        components: {
            'input-field': {
                template: '<div class="input-field col" :class="[ cols ]">' +
                          '     <input :id="name | format" :class="{ \'validate\': validate, \'invalid\': invalid  }" :required="required" :aria-required="required"' +
                          '            :name="name | format" :type="type" :value="value" :disabled="disabled" @input="updateValue($event.target.value)">' +
                          '     <label :for="name | format" :data-error="error">{{ name }} {{ comment }}</label>' +
                          '</div>',
                props   : {
                    name    : {
                        type: String
                    },
                    type    : {
                        type     : String,
                        'default': 'text'
                    },
                    value   : {
                        type: String
                    },
                    comment : {
                        type     : String,
                        'default': ''
                    },
                    cols    : {
                        type     : String,
                        'default': 's12 m6'
                    },
                    validate: {
                        type     : Boolean,
                        'default': false
                    },
                    invalid : {
                        type     : Boolean,
                        'default': false
                    },
                    error   : {
                        type     : String,
                        'default': ''
                    },
                    required: {
                        type     : Boolean,
                        'default': false
                    },
                    disabled: {
                        type     : Boolean,
                        'default': false
                    }
                },
                methods : {
                    updateValue: function (value) {
                        this.$emit('input', value);
                    }
                },
                filters : {
                    format: function (value) {
                        if (!value) return '';
                        return value.toSpinalCase();
                    }
                }
            }
        }
    }
);
