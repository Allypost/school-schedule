window.attendeesVM = new Vue({
    el     : '#attendees',
    data   : {
        teaching : {},
        attendees: {}
    },
    methods: {
        getTeachingUrl      : function () {
            return $(this.$el).attr('data-teaching');
        },
        fetchTeaching       : function () {
            var vm  = this;
            var url = vm.getTeachingUrl();

            $.get(url)
             .done(function (d) {
                 d.data.forEach(vm.editTeaching);
                 vm.fetchAttendees();
                 vm.loading = false;
             });
        },
        editTeaching        : function (lesson, update) {
            this.$edit(this.teaching, lesson.id, lesson);

            if (update && typeof update === typeof true)
                this.fetchLessonAttendees(lesson.id);
        },
        removeTeaching      : function (lessonID) {
            this.$delete(this.teaching, lessonID);
            this.$delete(this.attendees, lessonID);
        },
        getAttendeesUrl     : function (lesson) {
            var url = $(this.$el).attr('data-attendees');

            return url.replace(':lesson', lesson);
        },
        fetchAttendees      : function () {
            var vm  = this;
            var url = vm.getAttendeesUrl(' ').slice(0, -1);

            $.get(url)
             .done(function (d) {
                 d.data.forEach(vm.editAttendee)
             });
        },
        editAttendee        : function (data) {
            return this.$edit(this.attendees, data.id, data);
        },
        fetchLessonAttendees: function (lesson) {
            var vm  = this;
            var url = vm.getAttendeesUrl(lesson);

            $.get(url)
             .done(function (d) {
                 vm.editLessonAttendee(lesson, d.data);
             });
        },
        editLessonAttendee  : function (lesson, attendee) {
            this.$set(this.attendees, lesson, attendee);
        },
        btnStatus           : function (status, cb) {
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
        },
        $edit               : function ($object, key, data) {
            var vm = this;
            vm.$set($object, key, data);

            vm.$nextTick(function () {
                var object     = $object[ key ];
                var objectKeys = Object.keys(object);
                var dataKeys   = Object.keys(data);

                objectKeys.forEach(function (i) {
                    if (dataKeys.indexOf(i) < 0)
                        vm.$delete(object, i);
                })
            });
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
