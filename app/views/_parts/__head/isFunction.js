function isFunction(fn) {
    return !!(fn && fn.constructor && fn.call && fn.apply);
}
