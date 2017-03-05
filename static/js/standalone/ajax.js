function request(url) {
    var self = this;

    function getContentType(contentTypeHeader) {
        var startIndex = contentTypeHeader.indexOf('/') + 1;
        var endIndex   = contentTypeHeader.indexOf(';');

        if (endIndex <= 0)
            endIndex = contentTypeHeader.length;

        return contentTypeHeader.substring(startIndex, endIndex);
    }

    function handleStateChange(httpRequest, success, error) {

        function handleCallbacks(httpRequest, success, error) {
            if (httpRequest.status === 200) {
                if (isFunction(success))
                    self.success(success);

                suc(httpRequest);
            } else {
                if (isFunction(error))
                    self.error(success);

                err(httpRequest);
            }

            if (isFunction(alw))
                alw(httpRequest);
        }

        return function () {
            if (httpRequest.readyState !== XMLHttpRequest.DONE)
                return;

            var contentTypeHeader = httpRequest.getResponseHeader('Content-Type');
            var contentType       = getContentType(contentTypeHeader);

            if (contentType === 'json')
                httpRequest.responseJSON = JSON.parse(httpRequest.responseText);

            handleCallbacks(httpRequest, success, error);

            return this;
        }

    }

    function paramify(object) {
        var str = "";
        object  = object || {};
        for (var key in object) {
            if (!object.hasOwnProperty(key))
                continue;

            if (str != "")
                str += "&";

            str += key + "=" + encodeURIComponent(object[ key ]);
        }

        return str;
    }

    var suc = new Function();

    var err = new Function();

    var alw = new Function();

    this.make = function (type, data, success, error) {
        var httpRequest = new XMLHttpRequest();

        if (!httpRequest) {
            alert('There was a critical error. Please try again.');
            return false;
        }

        httpRequest.onreadystatechange = handleStateChange(httpRequest, success, error);

        httpRequest.open(type, url, true);
        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        httpRequest.send(paramify(data));

        return this;
    };

    this.post = function (data, success, error) {
        return this.make('POST', data, success, error);
    };

    this.get = function (success, error) {
        return this.make('GET', [], success, error);
    };

    this.makeAPI = function (type, data, success, error) {

        return this.make(
            type,
            data,
            function (req) {
                var json = req.responseJSON || {};
                var data = json.data || [];

                if (!isFunction(success))
                    success = new Function();

                success(data, req, json);
            },
            function (req) {
                var json = req.responseJSON || {};
                var data = json.errors || [ "Something went wrong" ];

                if (!isFunction(error))
                    error = new Function();

                error(data, req, json);
            }
        );

    };

    this.getAPI = function (success, error) {
        return this.makeAPI('GET', [], success, error);
    };

    this.postAPI = function (data, success, error) {
        var csrf = getCsrf();

        data[ csrf.key ] = csrf.value;

        return this.makeAPI('POST', data, success, error);
    };

    this.error = function (cb) {
        if (!isFunction(cb))
            cb = new Function();

        err = cb;
    };

    this.success = function (cb) {
        if (!isFunction(cb))
            cb = new Function();

        suc = cb;
    };

    this.always = function (cb) {
        if (!isFunction(cb))
            cb = new Function();

        alw = cb;
    };

    return this;
}
