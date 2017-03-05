window.lessonsVM = new Vue({
    el     : '#lessons',
    data   : {
        lessons    : [],
        loading    : true,
        error      : false,
        success    : false,
        btnInterval: null
    },
    methods: {
        getAttendingUrl: function () {
            return $(this.$el).attr('data-attending');
        },
        fetchLessons   : function () {
            var vm  = this;
            var url = vm.getAttendingUrl();

            $.get(url)
             .done(function (d) {
                 vm.lessons = d.data;
                 vm.loading = false;
             });
        },
        getLessons     : function () {
            var l = this.lessons;

            var lessons = {};

            for (var id in l) {
                if (!l.hasOwnProperty(id))
                    continue;

                var lesson = l[ id ];

                lessons[ lesson.id ] = { id: lesson.id, attending: !!lesson.attending };
            }

            return lessons;
        },
        saveLessons    : function () {
            var vm    = this;
            var $form = $('#lessons');
            var url   = $form.attr('action');

            var $csrfInput = $form.find('input[type="hidden"]:last');
            var csrf       = {
                key  : $csrfInput.attr('name'),
                token: $csrfInput.attr('value')
            };

            var data = {
                lessons: this.getLessons()
            };

            data[ csrf.key ] = csrf.token;


            this.loading = true;

            $.post(url, data)
             .done(function () {
                 vm.btnStatus('success');
             })
             .fail(function (data) {
                 data = data.responseJSON || { errors: [ 'Something went wrong' ] };

                 vm.btnStatus('error');

                 var errors = data.errors;

                 for (var e in errors) {
                     if (!errors.hasOwnProperty(e))
                         continue;

                     Materialize.toast(errors[ e ], 5000, 'status error');
                 }
             })
             .always(function () {
                 vm.loading = false;
             });
        },
        btnStatus      : function (status, cb) {
            var vm = this;

            vm[ status ] = true;

            window.clearInterval(vm.btnInterval);

            var fn = function () {
                vm[ status ] = false;

                cb = cb || function () {
                    };

                cb();
            };

            if (!cb)
                vm.btnInterval = window.setTimeout(fn, 1500);

            return fn;
        }
    },
    mounted: function () {
        $(document).ready(function () {
            lessonsVM.fetchLessons();
        });
    }
});
