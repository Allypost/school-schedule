window.notificationVM = new Vue(
    {
        el     : '#notifications',
        data   : {
            users        : {},
            notifications: {},
            teaching     : {},
            loading      : 0,
            isLoaded     : false
        },
        filters: {
            date: function (val) {
                return moment.utc(val).format('L');
            },
            time: function (val) {
                return moment.utc(val).format('L LT');
            }
        },
        methods: {
            getUsersUrl        : function () {
                return $(this.$el).attr('data-users');
            },
            fetchField         : function (url, cb) {
                var vm = this;

                var hash = url.hash();

                vm.loading ^= hash;

                if (!isFunction(cb))
                    cb = new Function;

                return $.get(url)
                        .done(function (d) {
                            var data = d.data;

                            cb(true, data);
                            $('.collapsible').collapsible();
                        })
                        .fail(function (d) {
                            var data = d.errors;

                            cb(false, data);
                        })
                        .always(function () {
                            vm.loading ^= hash;
                        });
            },
            fetchUsers         : function (cb) {
                var vm  = this;
                var url = vm.getUsersUrl();

                return vm.fetchField(url, function (success, data) {
                    if (success)
                        vm.setUsers(data);

                    if (!isFunction(cb))
                        cb = new Function;
                    cb();
                });
            },
            setUsers           : function (data) {
                this.$set(this, 'users', data);
            },
            getTeachingUrl     : function () {
                return $(this.$el).attr('data-teaching');
            },
            fetchTeaching      : function (cb) {
                var vm  = this;
                var url = this.getTeachingUrl();

                return vm.fetchField(url, function (success, data) {
                    if (success)
                        vm.setTeaching(data);

                    if (!isFunction(cb))
                        cb = new Function;

                    cb();
                });
            },
            setTeaching        : function (data) {
                var d = {};

                for (var i in data) {
                    if (!data.hasOwnProperty(i))
                        continue;

                    var datum = data[ i ];

                    datum.unseen = 0;

                    d[ datum.id ] = datum;
                }

                this.$set(this, 'teaching', d);
            },
            getNotificationsUrl: function () {
                return $(this.$el).attr('data-notifications');
            },
            fetchNotifications : function (cb) {
                var vm  = this;
                var url = this.getNotificationsUrl();

                return vm.fetchField(url, function (success, data) {
                    if (success)
                        vm.setNotifications(data);

                    if (!isFunction(cb))
                        cb = new Function;
                    cb();
                });
            },
            setNotifications   : function (data) {
                var d = {};

                for (var i in data) {
                    if (!data.hasOwnProperty(i))
                        continue;

                    d[ i ] = data[ i ].reverse();

                    for (var j in d[ i ]) {
                        if (!d[ i ].hasOwnProperty(j))
                            continue;

                        d[ i ][ j ].unseen = 0;
                    }
                }

                this.$set(this, 'notifications', d);
            },
            fetchData          : function (cb) {
                var vm = this;

                if (!isFunction(cb))
                    cb = new Function;

                var onComplete = function () {
                    return vm.$nextTick(function () {

                        if (vm.loading === 0) {

                            vm.hydrateUnseen();
                            $('.collapsible').collapsible();

                            cb();
                        }

                    });
                };

                vm.fetchUsers(onComplete);
                vm.fetchTeaching(onComplete);
                vm.fetchNotifications(onComplete);
            },
            seen               : function (notification, user) {
                var notif = moment.utc(notification.date);
                var usr   = moment.utc(user.seen);

                return notif.isBefore(usr);
            },
            hydrateUnseen      : function () {
                var vm               = this;
                var notificationList = vm.notifications;
                var lessons          = vm.teaching;
                var usersList        = vm.users;

                var list = {};

                for (var lesson in notificationList) {
                    if (!notificationList.hasOwnProperty(lesson))
                        continue;

                    list[ lesson ] = 0;

                    var notifications = notificationList[ lesson ];

                    for (var i in notifications) {
                        if (!notifications.hasOwnProperty(i))
                            continue;

                        var users = usersList[ lesson ];

                        if (!users)
                            continue;

                        var notification = notifications[ i ];

                        for (var k in users) {
                            if (!users.hasOwnProperty(k))
                                continue;

                            var user = users[ k ];
                            var seen = this.seen(notification, user);

                            notification.unseen += !seen;
                            list[ lesson ] += !seen;

                            if (!seen)
                                vm.$set(notification, 'unseen', notification.unseen);
                        }
                    }

                }

                for (var lessonID in list)
                    vm.$set(lessons[ lessonID ], 'unseen', list[ lessonID ]);

                return list;
            }
        },
        mounted: function () {
            var vm = this;

            vm.fetchData();

            vm.isLoaded = true;

            setInterval(vm.fetchData, 2 * 60 * 1000);

            moment.locale('en-gb');
        }
    }
);
