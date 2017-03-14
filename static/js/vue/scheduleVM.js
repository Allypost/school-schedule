function getWeek() {
    var now = new Date();
    var onejan = new Date(now.getFullYear(), 0, 1);
    var millisecsInDay = 86400000;
    var realWeek = Math.ceil((((now - onejan) / millisecsInDay) + onejan.getDay() + 1) / 7);

    return realWeek % 2 + 1;
}

var getScheduleMatrix = function() {
    var key = 'scheduleVM:schedule:matrix';

    function generate() {
        var scheduleMatrix = {};

        var weeks = [ 1, 2 ];
        var days = [ 'mon', 'tue', 'wed', 'thu', 'fri' ];

        var periods = Array.apply(null, new Array(5)).map(function(_, i) {
            return i + 1;
        });

        for (var week in weeks) {
            if (!weeks.hasOwnProperty(week))
                continue;

            var w = weeks[ week ];

            if (!scheduleMatrix[ w ])
                scheduleMatrix[ w ] = {};


            for (var day in days) {
                if (!days.hasOwnProperty(day))
                    continue;

                var d = days[ day ];

                if (!scheduleMatrix[ w ][ d ])
                    scheduleMatrix[ w ][ d ] = {};


                for (var period in periods) {
                    if (!periods.hasOwnProperty(period))
                        continue;

                    var p = periods[ period ];

                    scheduleMatrix[ w ][ d ][ p ] = {
                        id: null,
                        owner: null,
                        name: '',
                        subject: '',
                        status: '',
                        due: null,
                        week: null,
                        day: null,
                        period: null,
                        owned: false,
                        hasClass: false,
                        isLoading: false,
                    };
                }
            }
        }

        return save(scheduleMatrix);
    }

    function save(matrix) {
        if (localStorage && localStorage.setItem)
            localStorage.setItem(key, JSON.stringify(matrix));

        return matrix;
    }

    function retrieve() {
        if (!(localStorage && localStorage.getItem))
            return false;

        var resRaw = localStorage.getItem(key);

        return resRaw && JSON.parse(resRaw);
    }

    return retrieve() || generate();
};

window.scheduleVM = new Vue(
    {
        el: '#schedule',
        data: {
            week: getWeek(),
            days: [ 'mon', 'tue', 'wed', 'thu', 'fri' ],
            schedule: getScheduleMatrix(),
            teaching: {},
            statuses: { Cancelled: 'Cancelled', Normal: '' },
            lastFetch: 0,
            isLoaded: false,
        },
        computed: {
            isTeacher: function() {
                return !!parseInt($(this.$el).attr('data-teacher'));
            },
        },
        filters: {
            lower: function(el) {
                return el.toString().toLowerCase();
            },
            capitalize: function(value) {
                if (!value) return '';
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            },
        },
        methods: {
            getLocation: function(subject) {
                return {
                    week: subject.week,
                    day: subject.day,
                    period: subject.period,
                };
            },
            subjectChange: function(options, data, location) {
                var newSubject = options[ options.selectedIndex ];
                var nsID = Number(newSubject.value);
                var nsName = newSubject.innerText;

                data.week = location.week;
                data.day = location.day;
                data.period = location.period;
                data.name = nsName;
                data.hasClass = true;
                data.owned = true;
                data.isLoading = true;

                location.subject = nsID;

                this.subjectPropagate(location, function(d, status) {
                    data.isLoading = false;

                    if (status == 'error') {
                        Materialize.toast('An error occured', 3000, 'status error');
                        return;
                    }

                    data.hasClass = d.data.new.hasClass;
                });
            },
            setStatus: function(data, newStatus, location) {
                data.status = newStatus;
                data.isLoading = true;

                location.status = newStatus;

                this.statusPropagate(location, function(d, status) {
                    data.isLoading = false;

                    if (status == 'error') {
                        Materialize.toast('An error occured', 3000, 'status error');
                    }
                });
            },
            statusPropagate: function(data, cb) {
                var url = $(this.$el).attr('data-status');
                return this.dataPropagate(data, url, cb);
            },
            subjectPropagate: function(data, cb) {
                var url = $(this.$el).attr('data-subject');
                return this.dataPropagate(data, url, cb);
            },
            dataPropagate: function(data, url, cb) {
                data[ $('meta[name="csrf_key"]').attr('content') ] = $('meta[name="csrf"]').attr('content');

                return $.post(url, data)
                        .done(function(data) {
                            cb(data, 'success');
                        })
                        .fail(function(data) {
                            cb(data, 'error');
                        });
            },
            highlightClass: function(cls) {
                if (cls.owned)
                    return;

                var l = this.getLocation(cls);
                var cl = 'schedule-hour-highlight';
                var id = 'span[data-week=\'{0}\'][data-day=\'{1}\'][data-period=\'{2}\']'.format(l.week, l.day, l.period);

                var $el = $(id).parent();

                $el.addClass(cl);
                setTimeout(function() {
                    $el.removeClass(cl);
                }, 850);
            },
            getScheduleUrl: function() {
                return $(this.$el).attr('data-schedule');
            },
            fetchSchedule: function(cb, recent) {
                var vm = this;
                var url = vm.getScheduleUrl();

                if (recent)
                    url += '/' + this.lastFetch;

                $.get(url)
                 .done(function(data) {
                     cb(data, vm, recent);
                     vm.lastFetch = data.timestamp;
                 })
                 .fail(function() {
                     Materialize.toast('Couldn\'t get schedule!', 3000, 'status error');
                 });
            },
            setSchedule: function(classes, notify) {
                for (var c in classes) {
                    if (!classes.hasOwnProperty(c))
                        continue;

                    var cl = classes[ c ];
                    var w = cl.week;
                    var d = cl.day;
                    var p = cl.period;

                    cl[ 'isLoading' ] = false;
                    this.schedule[ w ][ d ][ p ] = cl;

                    if (notify)
                        this.highlightClass(this.schedule[ w ][ d ][ p ]);
                }

                this.$forceUpdate();
            },
            getTeachingUrl: function() {
                return $(this.$el).attr('data-teaching');
            },
            fetchTeaching: function(cb) {
                var vm = this;
                var url = vm.getTeachingUrl();
                $.get(url)
                 .done(function(data) {
                     cb(data, vm);
                 })
                 .fail(function() {
                     Materialize.toast('Couldn\'t get classes!', 3000, 'status error');
                 });
            },
            setTeaching: function(data) {
                for (var id in data) {
                    if (!data.hasOwnProperty(id))
                        continue;

                    var subject = data[ id ];

                    this.teaching[ subject.id ] = subject;
                }

                this.$forceUpdate();
            },
        },
        mounted: function() {
            var vm = this;

            $(document).ready(function() {
                scheduleVM.fetchSchedule(function(data, vm) {
                    vm.setSchedule(data.data);
                });

                if (vm.isTeacher) {
                    scheduleVM.fetchTeaching(function(data, vm) {
                        var d = data.data;

                        vm.teaching[ 0 ] = {
                            due: null,
                            id: -1,
                            name: '/',
                            owner: -1,
                            status: '',
                        };

                        vm.setTeaching(d);
                    });
                }

                window.setInterval(function() {
                    scheduleVM.fetchSchedule(function(data, vm) {
                        vm.setSchedule(data.data, true);
                    }, true);
                }, 5000);

                $('.dropdown-button').dropdown();

                vm.isLoaded = true;
            });
        },
    });
