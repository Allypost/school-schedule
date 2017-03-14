window.lessonsVM = new Vue(
    {
        el: '#lessons',
        data: {
            lessons: [],
            newLesson: '',
            loading: true,
            deleting: false,
            deleted: false,
            deletedError: false,
            error: false,
            success: false,
            isEditing: false,
            lessonEditing: null,
            datePicker: null,
            dateFormats: {
                db: 'YYYY-MM-DD',
                moment: 'dddd D MMM YYYY',
                picker: 'DD d M yyyy',
            },
        },
        filters: {
            formatDate: function(date) {
                if (!date)
                    return '';
                return moment(date, lessonsVM.dateFormats.db).format(lessonsVM.dateFormats.moment);
            },
            reverseFormatDate: function(date) {
                if (!date)
                    return '';
                return moment(date, lessonsVM.dateFormats.moment).format(lessonsVM.dateFormats.db);
            },
        },
        methods: {
            fetchLessons: function() {
                var vm = this;
                var url = $(this.$el).attr('data-fetch-url');

                $.get(url)
                 .done(function(d) {
                     vm.lessons = d.data;
                     vm.loading = false;
                 });
            },
            getLesson: function() {
                var csrf = getCsrf();

                var data = {
                    'name': this.newLesson,
                };

                if (this.isEditing) {
                    var lesson = this.lessonEditing;

                    data[ 'subject' ] = lesson.id;
                    data[ 'due' ] = lesson.due;
                }

                data[ csrf.key ] = csrf.value;

                return data;
            },
            dateSelect: function(date) {
                var d = moment(date);
                this.dateSet(d);
            },
            dateHide: function(inst) {
                var date = inst.$el[ 0 ].value;

                if (!date)
                    return this.dateSet(null);

                var formattedDate = this.$options.filters.reverseFormatDate(date);
                this.dateSelect(formattedDate);
            },
            dateSet: function(momentDate) {
                if (!this.lessonEditing)
                    return;

                if (momentDate === null)
                    return this.lessonEditing.due = null;

                if (momentDate.isValid())
                    this.lessonEditing.due = momentDate.format(this.dateFormats.db);
            },
            editLesson: function(lesson) {
                this.clearLesson();

                this.newLesson = lesson.name;
                this.isEditing = true;
                this.lessonEditing = lesson;

                Vue.set(lesson, 'isEdited', true);

                this.refreshDatepicker();
                if (lesson.due)
                    this.datePicker.selectDate(new Date(lesson.due));

                this.$nextTick(function() {
                    Materialize.updateTextFields();
                });
            },
            cancelLesson: function() {
                this.clearLesson();
            },
            saveLesson: function() {
                var vm = this;

                vm.loading = true;

                var url = this.$el.dataset.saveUrl;
                var data = this.getLesson();

                $.post(url, data)
                 .done(function(d) {
                     if (vm.isEditing)
                         vm.saveEditedLesson(d);
                     else
                         vm.saveNewLesson(d);

                     vm.clearLesson();
                     vm.btnStatus('success');
                 })
                 .fail(function(data) {
                     vm.displayErrors(data);
                 })
                 .always(function() {
                     vm.loading = false;
                 });
            },
            deleteLesson: function() {
                var vm = this;
                swal(
                    {
                        title: 'Delete lesson',
                        text: 'Are you sure you want to delete the lesson?',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        allowOutsideClick: true,
                        confirmButtonColor: '#f44336',
                    }
                ).then(
                    function() {
                        vm.deletePropagate();
                    },
                    function() {

                    }
                );
            },
            deletePropagate: function(cb) {
                var data = this.getLesson();
                var vm = this;
                var url = this.$el.dataset.deleteUrl;

                if (!cb)
                    cb = function() {
                    };

                vm.loading = true;
                vm.deleting = true;

                $.post(url, data)
                 .done(function() {
                     vm.lessons.splice(vm.lessons.indexOf(vm.lessonEditing), 1);

                     Materialize.toast('Lesson deleted', 1500, 'status success');

                     vm.btnStatus('deleted');
                     vm.clearLesson();

                     if (window.attendeesVM)
                         window.attendeesVM.removeTeaching(data.subject);

                     cb(true);
                 })
                 .fail(function(data) {
                     vm.displayErrors(data);
                     vm.btnStatus('deletedError');
                     cb(false);
                 })
                 .always(function() {
                     vm.loading = false;
                     vm.deleting = false;
                 });
            },
            saveNewLesson: function(data) {
                var d = data.data;

                this.lessons.push(d);

                if (window.attendeesVM)
                    window.attendeesVM.editTeaching(d);
            },
            saveEditedLesson: function(data) {
                var lesson = this.lessonEditing;
                var d = data.data;

                lesson.name = d.name;

                if (window.attendeesVM)
                    window.attendeesVM.editTeaching(d, true);
            },
            clearLesson: function() {
                if (this.lessonEditing)
                    Vue.set(this.lessonEditing, 'isEdited', false);

                this.newLesson = '';
                this.isEditing = false;
                this.lessonEditing = null;

                this.destroyDatepicker();

                this.$nextTick(function() {
                    Materialize.updateTextFields();
                });
            },
            btnStatus: function(status, cb) {
                var vm = this;

                vm[ status ] = true;

                var fn = function() {
                    vm[ status ] = false;

                    cb = cb || function() {
                    };

                    cb();
                };

                if (!cb)
                    window.setTimeout(fn, 1500);

                return fn;
            },
            displayErrors: function(data) {
                data = data.responseJSON || { errors: [ 'Something went wrong' ] };

                this.btnStatus('error');

                var errors = data.errors;

                for (var e in errors) {
                    if (!errors.hasOwnProperty(e))
                        continue;

                    Materialize.toast(errors[ e ], 5000, 'status error');
                }
            },
            destroyDatepicker: function() {
                if (!this.datePicker)
                    return;
                this.datePicker.destroy();
                this.datePicker = null;
            },
            refreshDatepicker: function() {
                this.destroyDatepicker();
                this.createDatepicker();
            },
            createDatepicker: function() {
                var vm = this;
                var threeDaysFromNow = new Date();
                var days3FromNow = threeDaysFromNow.getDate() + 3;

                threeDaysFromNow.setDate(days3FromNow);

                var opts = {
                    language: 'en',
                    minDate: threeDaysFromNow,
                    dateFormat: this.dateFormats.picker,
                    autoClose: true,
                    clearButton: true,
                    onSelect: function(formattedString, date) {
                        vm.dateSelect(date);
                    },
                    onHide: function(inst, animtaionCompleted) {
                        if (!animtaionCompleted)
                            return;
                        vm.dateHide(inst);
                    },
                    onRenderCell: function(date, cellType) {
                        var disabledDays = [ 0, 6 ];
                        if (cellType == 'day') {
                            var day = date.getDay(),
                                isDisabled = disabledDays.indexOf(day) != -1;

                            return {
                                disabled: isDisabled,
                            };
                        }
                    },
                };

                var datepicker = $('#datepicker').datepicker().data('datepicker');

                datepicker.update(opts);

                this.datePicker = datepicker;
            },
        },
        mounted: function() {
            this.createDatepicker();
        },
    });
