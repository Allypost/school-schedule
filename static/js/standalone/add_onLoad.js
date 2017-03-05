function addLoadEvent(func) {
    var oldonload = window.onload;

    if (typeof window.onload != typeof (new Function)) {
        window.onload = func;
    } else {
        window.onload = function () {
            if (oldonload) {
                oldonload();
            }
            func();
        }
    }
}
