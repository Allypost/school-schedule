window.notificationVM = new Vue(
    {
        el     : '#notifications',
        data   : {
            newer: [],
            older: []
        },
        filters: {
            ago: function (value) {
                return moment.utc(value, 'YYYY-MM-DD HH:mm:ss').fromNow();
            }
        },
        methods: {
            getFetchUrl: function () {
                return this.$el.dataset.fetchUrl;
            },
            fetchAll   : function () {
                var vm  = this;
                var url = vm.getFetchUrl();

                $.get(url)
                 .done(function (data) {
                     var d = data.data;

                     vm.newer = d.new;
                     vm.older = d.old;
                 })
            }
        },
        mounted: function () {
            this.fetchAll();
        }
    }
);
