window.topbarVM = new Vue(
    {
        el: '.topbar',
        data: {
            notifications: [],
            notificationCount: 0,
            notificationCountLoading: false,
            notificationsLoading: false,
        },
        methods: {
            getListUrl: function() {
                return this.$el.dataset.notificationsList;
            },
            getCountUrl: function() {
                return this.$el.dataset.notificationsCount;
            },
            getData: function() {
                if (this.notificationsLoading)
                    return;

                var vm = this;
                var url = this.getListUrl();

                vm.notificationsLoading = true;

                $.get(url)
                 .done(function(data) {
                     var d = data.data;

                     vm.notifications = d;
                     vm.notificationCount = d ? d.length : 0;
                     vm.notificationsLoading = false;
                 });
            },
            getCount: function() {
                if (this.notificationCountLoading)
                    return;

                var vm = this;
                var url = this.getCountUrl();

                vm.notificationCountLoading = true;

                $.get(url)
                 .done(function(data) {
                     vm.notificationCount = data.data.count;
                     vm.notificationCountLoading = false;
                 });
            },
            dropdownGet$: function() {
                return $(this.$refs.dropdown);
            },
            dropdownToggle: function() {
                var vm = this;
                var el = vm.$refs.dropdown;
                var url = vm.$el.dataset.notificationsSeen;
                var data = {};
                var csrf = getCsrf();

                if (el.className.indexOf('active') < 0)
                    return this.getData();

                data[ csrf.key ] = csrf.value;

                $.post(url, data)
                 .done(function() {
                     vm.notificationCount = 0;
                 });
            },
            dropdownRegister: function() {
                this.dropdownGet$().dropdown(
                    {
                        inDuration: 300,
                        outDuration: 225,
                        constrain_width: false,
                        hover: false,
                        gutter: 0,
                        belowOrigin: true,
                        alignment: 'right',
                    }
                );
            },
        },
        mounted: function() {
            var vm = this;

            vm.dropdownRegister();
            $('.button-collapse').sideNav();

            $(document).ready(function() {
                vm.getData();
            });
        },
    });
