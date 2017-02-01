var cp;
var inviteVM = new Vue(
    {
        el        : '#invite-user',
        data      : {
            loading : false,
            sexes   : [
                'male',
                'female',
                'intersex'
            ],
            btnError: false
        },
        computed  : {
            sexez: function () {
                var sexesValues = [];
                var sexes       = this.sexes;

                for (var i in sexes) {
                    if (!sexes.hasOwnProperty(i))
                        continue;

                    sexesValues.push(sexes[ i ][ 0 ].toLowerCase());
                }

                return sexesValues;
            }
        },
        methods   : {
            getFormData: function () {
                return $(this.$el).serializeObject();
            },
            getData    : function () {
                var formData = this.getFormData();

                formData[ 'sex' ] = formData[ 'sex' ] || this.getSex();

                return formData;
            },
            getUrl     : function () {
                return $(this.$el).attr('action');
            },
            getSex     : function () {
                var currSex = $(this.$refs.sex).siblings().filter("input").val().trim()[ 0 ].toLowerCase();
                var valid   = this.sexez;

                for (var i in valid) {
                    if (!valid.hasOwnProperty(i))
                        continue;

                    if (currSex == valid[ i ])
                        return currSex;
                }

                return null;
            },
            send       : function (nudes) {
                var vm   = this;
                var data = this.getData();
                var url  = this.getUrl();

                vm.loading = true;

                $.post(url, data)
                 .done(function (data) {
                     var d       = data.data;
                     var u       = d.user;
                     var baseUrl = "https://is.gd/create.php?format=json&callback=?&url=";
                     var apiUrl  = baseUrl + encodeURIComponent(d.url);
                     var id      = 'user-id-' + u.uuid;

                     var pre = $("<div class='row swal-pre'>" +
                                 "<div class='col s12'> Name: <span data-inv-name></span></div>" +
                                 "<div class='col s12'>Email: <span data-inv-email></span></div>" +
                                 "<div class='col s12'>   ID: <span data-inv-id></span></div>" +
                                 "<div class='col s12'> Link: <a href='' data-inv-link>Creation link</a></div>" +
                                 "</div>");

                     pre.attr('id', id);

                     pre.find('span[data-inv-name]').text(u.name);
                     pre.find('span[data-inv-email]').text(u.email);
                     pre.find('span[data-inv-id]').text(u.uuid);
                     pre.find('a[data-inv-link]').attr('href', d.url);

                     swal(
                         {
                             title            : 'Invite created',
                             html             : pre,
                             type             : 'success',
                             allowOutsideClick: false,
                             allowEscapeKey   : false
                         }
                     );

                     $.getJSON(apiUrl, function (d) {
                         $('#' + id).append($('<div class="col s12">  URL: <button class="btn btn-flat btn-cpy" data-clipboard-text=' + d.shorturl + '>Click to copy</button></div>'));

                         cp = cp || new Clipboard('.btn-cpy');

                         cp.on('success', function () {
                             Materialize.toast('Copied to clipboard', 1500, 'status success');
                         });
                     });
                 })
                 .fail(function (data) {
                     data  = data.responseJSON || { errors: [ 'Something went wrong!' ] };
                     var d = data.errors;

                     for (var i in d) {
                         if (!d.hasOwnProperty(i))
                             continue;

                         Materialize.toast(d[ i ], 3000, 'status error');
                     }

                     vm.btnStatus('btnError');
                 })
                 .always(function () {
                     vm.loading = false;
                 });
            },
            btnStatus  : function (status) {
                var vm = this;

                this[ status ] = true;

                var fn = function () {
                    vm[ status ] = false;
                };

                return window.setTimeout(fn, 1500);
            }
        },
        components: {
            'input-field': {
                template: '<div class="input-field col" :class="[ cols ]">' +
                          /*    */'<input ref="input" :id="name | format" :class="{ \'validate\': validate }" :required="required" :aria-required="required" ' +
                          /*           */':name="name | format" :type="type" :value="value" @input="updateValue($event.target.value)">' +
                          /*    */'<label :for="name | format">{{ name }} {{ comment }}</label>' +
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
                    required: {
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
        },
        mounted   : function () {
            var now = new Date();

            $('select').material_select();

            $('#dob').datepicker(
                {
                    language  : 'en',
                    maxDate   : new Date(new Date().setFullYear(now.getFullYear() - 7)),
                    minDate   : new Date(new Date().setFullYear(now.getFullYear() - 100)),
                    view      : 'years',
                    autoClose : true,
                    dateFormat: 'dd/mm/yyyy'
                }
            );
        }
    }
);
