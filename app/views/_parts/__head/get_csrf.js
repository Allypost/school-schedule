function getCsrf() {
    return {
        'key'  : $('meta[name="csrf_key"]').attr('content'),
        'value': $('meta[name="csrf"]').attr('content')
    }
}
