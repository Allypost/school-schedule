function addJs(fileName) {

    var head   = document.head;
    var script = document.createElement('script');

    script.type = 'application/javascript';
    script.src  = fileName;

    head.appendChild(script);
}
