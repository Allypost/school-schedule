window.attendeesVM = new Vue({
    el     : '#attendees',
    data   : {
        teaching: [],
        users   : {}
    },
    methods: {
        getTeachingUrl : function () {
            return $(this.$el).attr('data-lessons');
        },
        getAttendeesUrl: function (lesson) {
            var url = $(this.$el).attr('data-users');

            return url.replace(':lesson', lesson);
        },
        fetchTeaching  : function () {
            var vm  = this;
            var url = vm.getTeachingUrl();

            $.get(url)
             .done(function (d) {
                 vm.teaching = d.data;
                 vm.fetchAttendees();
                 vm.loading = false;
             });
        },
        fetchAttendees : function () {
            var vm  = this;
            var url = vm.getAttendeesUrl(' ').slice(0, -1);

            $.get(url)
             .done(function (d) {
                 vm.$set(vm, 'users', d.data)
             });
        },
        getTeaching    : function () {
            var l = this.teaching;

            var teaching = {};

            for (var id in l) {
                if (!l.hasOwnProperty(id))
                    continue;

                var lesson = l[ id ];

                teaching[ lesson.id ] = { id: lesson.id, attending: !!lesson.attending };
            }

            return teaching;
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
        var vm = this;
        $(document).ready(function () {
            vm.fetchTeaching();

            window.setInterval(function () {
                vm.fetchTeaching();
            }, 60 * 1000);

            $('.collapsible').collapsible();
        });
    }
});
