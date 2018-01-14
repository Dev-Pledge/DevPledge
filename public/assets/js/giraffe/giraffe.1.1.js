/**
 *
 * @param initOptions
 * @constructor
 */
function Giraffe(initOptions, noInit) {

    noInit = noInit || false;
    this.initOptions(initOptions);
    this.$_GET = this.getQueryParams(document.location.search);

    if (noInit == false) {
        this.initAjaxQueue();
        this.initExecute();
    }
    this.environment = this.getCookie('GiraffeEnvironment') || 'production';
}

Giraffe.prototype.initAjaxQueue = function () {
    this.xhrQueue = {};
    var self = this;
    $(document).ajaxSend(function (event, jqxhr, settings) {
        jqxhr.GiraffeUniqueId = self.stringGen(6, 'ASDUYty15243');
        self.xhrQueue[jqxhr.GiraffeUniqueId] = jqxhr; //alert(settings.url);
        self.log('sent');
    });
    $(document).ajaxComplete(function (event, jqxhr, settings) {
        if (self.isset(jqxhr.GiraffeUniqueId) && self.isset(self.xhrQueue[jqxhr.GiraffeUniqueId])) {
            delete(self.xhrQueue[jqxhr.GiraffeUniqueId]);
        }
        self.log('done', jqxhr);
    });
}

Giraffe.prototype.ajaxAbort = function () {
    var self = this;
    var queueCount = self.ajaxQueueCount();
    self.log('in Ajax Queue: ' + queueCount);
    if (this.isset(self.xhrQueue)) {
        Object.keys(self.xhrQueue).forEach(function (key) {
            self.xhrQueue[key].abort();
        });

    }
}

Giraffe.prototype.ajaxQueueCount = function () {
    var self = this;
    if (this.isset(self.xhrQueue)) {
        var i = 0;
        Object.keys(self.xhrQueue).forEach(function (key) {
            i++
        });
        return i;
    }
    return 0;
}

Giraffe.prototype.initExecute = function () {
    var self = this;
    $(document).ready(function () {
        var rawData = {};
        rawData.initExecute = true;
        var jsonData = JSON.stringify(rawData);
        $.ajax({
            type: 'POST',
            url: self.getAjaxUrl(),
            data: jsonData,
            async: true
        }).done(function (resp) {
            if (self.isset(resp.executables)) {
                var i;
                for (i = 0; i < resp.executables.length; i++) {
                    self.parseResponse(resp.executables[i]);
                }
            }
            if (self.isset(resp.environment)) {
                self.environment = resp.environment;
                self.setCookie('GiraffeEnvironment', self.environment);
            }

        }).fail(function () {
            self.log('failed!');
        }).always(function (a, b) {
            self.log(a, b);
        });


    });

}


/**
 *
 * @param qs
 * @returns {{}}
 */
Giraffe.prototype.getQueryParams = function (qs) {
    qs = qs.split("+").join(" ");
    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])]
            = decodeURIComponent(tokens[2]);
    }

    return params;
}
/**
 *
 * @param initOptions
 */
Giraffe.prototype.initOptions = function (initOptions) {
    var self = this;
    this.initOptionsRunCount = this.initOptionsRunCount || 0;
    this.initOptionsRunCount++;
    initOptions = initOptions || {};
    if (this.initOptionsRunCount == 1) {
        this.dataTableSelector = '#dataTable';
        this.ajaxRoute = '/ajax';
        this.ajaxCaching = true;

        this.preAjaxFunction = function (controller, method, data) {
            return true;
        };

        this.postAjaxFunction = function (response, controller, method, data) {
            return true;
        };

        this.notificationFunction = function (message, type, style, position, timeout, title, thumbnail) {
            alert(message);
        };

        this.formErrorsFunction = function (formErrors, selector) {
            self.formErrors(formErrors, selector);
        }

        this.removeFormErrorsFunction = function (selector) {
            self.removeFormErrors(selector);
        }

        this.clearNotificationsFunction = function () {
            return true;
        }

        this.modalFunction = function (header, body) {
            alert(header + ' \n\n ' + body);
        }
        this.progressFunction = function (percentage) {
            self.log('progress:' + percentage + '%');
        }


    }

    Object.keys(initOptions).forEach(function (key) {
        var value = initOptions[key];
        switch (key) {

            case'preAjaxFunction':
                self.setIfFunction(value, 'preAjaxFunction');
                break;
            case 'postAjaxFunction':
                self.setIfFunction(value, 'postAjaxFunction');
                break;
            case 'ajaxUrl':
            case 'ajaxRoute':
                self.setIfString(value, 'ajaxRoute');
                break;
            case 'notificationFunction':
                self.setIfFunction(value, 'notificationFunction');
                break;
            case 'clearNotificationsFunction':
                self.setIfFunction(value, 'clearNotificationsFunction');
                break;
            case 'formErrorsFunction':
                self.setIfFunction(value, 'formErrorsFunction');
                break;
            case 'removeFormErrorsFunction':
                self.setIfFunction(value, 'removeFormErrorsFunction');
                break;
            case 'modalFunction':
                self.setIfFunction(value, 'modalFunction');
                break;
            case 'progressFunction':
                self.setIfFunction(value, 'progressFunction');
                break;
            case 'ajaxCache':
                self.ajaxCaching = value;
                break;

        }
    });
}
/**
 *
 * @param setToFn
 * @param fnLocation
 */
Giraffe.prototype.setIfFunction = function (setToFn, fnLocation) {
    if (typeof(setToFn) === 'function') {
        this[fnLocation] = setToFn;
    }
}
/**
 *
 * @param setToString
 * @param stringLocation
 */
Giraffe.prototype.setIfString = function (setToString, stringLocation) {
    if (typeof(setToString) === 'string') {
        this[stringLocation] = setToString;
    }
}
/**
 *
 * @param value
 * @returns {boolean}
 */
Giraffe.prototype.isset = function (value) {
    if (typeof(value) != 'undefined') {
        if (value === null) {
            return false;
        }
        return true;
    }
    return false;
}
/**
 *
 * @param e
 * @returns {boolean}
 */
Giraffe.prototype.browserPrevent = function (e) {
    if (typeof (e) === 'undefined')
        return false;

    if (e.preventDefault) {
        e.preventDefault();
    } else {
        e.stop();
    }

    e.returnValue = false;
    e.stopPropagation();
}
/**
 *
 * @param controller
 * @param method
 * @param data
 * @param callback
 */
Giraffe.prototype.ajax = function (controller, method, data, callback, selector, progressFunction) {
    callback = callback || function () {

        };
    progressFunction = progressFunction || this.progressFunction;
    selector = selector || false;
    var preAjax = this.preAjaxFunction(controller, method, data) || false;

    if (preAjax === false) {
        this.log('pre ajax function failed!');
        return;
    }
    var rawData = {};

    rawData.controller = controller;
    rawData.method = method;
    rawData.data = data;

    var jsonData = JSON.stringify(rawData);
    var self = this;
    $.ajax({
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            (xhr.upload || xhr).addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = (evt.loaded / evt.total) * 100;

                    progressFunction(percentComplete);
                }
            }, false);
            return xhr;
        },
        type: 'POST',
        url: self.getAjaxUrl(),
        data: jsonData,
        async: true
    }).done(function (resp) {

        var postAjax = self.postAjaxFunction(resp, controller, method, data);
        self.parseResponse(resp, selector);
        if (postAjax) {
            callback(resp.response);
        }

    }).fail(function () {
        self.log('failed!');
    }).always(function (a, b) {
        self.log(a, b);
    });
}

Giraffe.prototype.getAjaxUrl = function () {
    var endUrl = '';
    var url = '';
    if (this.ajaxCaching) {
        endUrl = '_=' + new Date().getTime();

        var parts = this.ajaxRoute.split('?');

        if (parts.length == 1) {
            url = this.ajaxRoute + '?' + endUrl;
        } else if (parts.length > 1) {
            url = this.ajaxRoute + '&' + endUrl;
        }
    } else {
        url = this.ajaxRoute;
    }

    return url;
}
/**
 *
 * @param controller
 * @param method
 * @param selector
 * @param callback
 */
Giraffe.prototype.ajaxUpload = function (controller, method, selector, callback, progressFunction) {
    selector = selector || 'form';
    callback = callback || function () {

        };
    progressFunction = progressFunction || function () {

        };
    var data = this.getAjaxDataFromForm(selector) || {};
    var preAjax = this.preAjaxFunction(controller, method, data) || false;

    if (preAjax === false) {
        this.log('pre ajax function failed!');
        return;
    }
    var rawData = {};
    rawData.controller = controller;
    rawData.method = method;
    rawData.data = data;

    var jsonData = JSON.stringify(rawData);
    var self = this;
    self.log(data);
    var uploadData = this.getAjaxUploadDataFromForm(selector);
    self.ajaxAbort();
    $.ajax({
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            (xhr.upload || xhr).addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = (evt.loaded / evt.total) * 100;
                    self.log(percentComplete);
                    progressFunction(percentComplete);
                }
            }, false);
            return xhr;
        },
        type: 'POST',
        url: self.getAjaxUrl(),
        data: uploadData,
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        headers: {
            'giraffe-json': jsonData
        }
    }).done(function (resp) {

        var postAjax = self.postAjaxFunction(resp, controller, method, data);

        self.parseResponse(resp, selector);
        if (postAjax) {
            callback(resp.response);
        }

    }).fail(function () {
        self.log('failed!');
    }).always(function (a, b) {
        self.log(a, b);
    });
}

/**
 *
 * @param controller
 * @param method
 * @param selector
 * @param callback
 */
Giraffe.prototype.ajaxForm = function (controller, method, selector, callback, preHook) {
    preHook = preHook || function () {
            return true;
        };
    var self = this;
    $(selector).on('submit', function (e) {
        self.browserPrevent(e);
        var data = self.getAjaxDataFromForm(selector);
        var pre = preHook(controller, method, data, callback, selector);
        if (pre) {
            self.ajax(controller, method, data, callback, selector);
        }
    });
}
/**
 *
 * @param controller
 * @param method
 * @param selector
 * @param callback
 */
Giraffe.prototype.ajaxUploadForm = function (controller, method, selector, callback, preHook, progressFunction) {
    preHook = preHook || function () {
            return true;
        }
    var self = this;
    $(selector).on('submit', function (e) {
        self.browserPrevent(e);
        var pre = preHook(controller, method, callback, selector);
        if (pre) {
            self.ajaxUpload(controller, method, selector, callback, progressFunction);
        }
    });
}
/**
 *
 * @param response
 * @param selector
 */
Giraffe.prototype.parseResponse = function (response, selector) {
    selector = selector || false;
    /**
     * Notifications
     */
    if ((this.isset(response.clearNotifications)) && response.clearNotifications) {

        this.clearNotificationsFunction();
    }
    if (this.isset(response.notifications)) {
        var i;
        var notifications = response.notifications;
        for (i = 0; i < notifications.length; i++) {
            var message = notifications[i].message;
            var type = notifications[i].type;
            var timeout = notifications[i].timeout;
            var delay = notifications[i].delay;
            this.notificationFunction(message, type, timeout, delay);
        }

    }

    if (this.isset(response.functions)) {
        var i;
        var functions = response.functions;
        for (i = 0; i < functions.length; i++) {
            var name = functions[i].name;
            var args = functions[i].arguments || [];
            var delay = functions[i].delay || 0;

            if (typeof(window[name]) === 'function') {
                setTimeout(function () {
                    window[name].apply(null, args);
                }, delay);
            }

        }
    }

    if (this.isset(response.refresh)) {
        setTimeout(function () {
            window.location.reload(response.refresh.force);
        }, response.refresh.delay || 0);
    }

    if (this.isset(response.redirect)) {
        setTimeout(function () {
            window.location.href = response.redirect.location;
        }, response.redirect.delay || 0);
    }
    this.removeFormErrorsFunction(selector);

    if (this.isset(response.formErrors)) {
        this.formErrorsFunction(response.formErrors, selector);
    }

    if ((this.isset(response.modal)) && this.isset(response.modal.header) && this.isset(response.modal.body)) {
        this.modal = this.modalFunction(response.modal.header, response.modal.body);
    }

    if (this.isset(response.views)) {
        this.appendView(response.views);
    }
}

Giraffe.prototype.appendView = function (views) {
    var self = this;
    Object.keys(views).forEach(function (key) {
        var view = views[key];
        if (self.isset($(key))) {
            if ($(key).length > 0) {
                $(key).html(view);
            }
        }

    });
}


Giraffe.prototype.removeFormErrors = function (selector) {
    selector = selector || false;
    if (selector) {
        $(selector + ' .error-message').remove();
    }

}


Giraffe.prototype.formErrors = function (formErrors, selector) {
    selector = selector || 'form';
    Object.keys(formErrors).forEach(function (key) {
        var value = formErrors[key];
        var field = $(selector + ' input[name="' + key + '"]').parent().find('.error-message');
        if (field.length > 0) {
            field.html(value);
        } else {
            $(selector + ' input[name="' + key + '"]').parent().append('<div class="error-message" style="color:red">' + value + '</div>');
        }
    });
}

Giraffe.prototype.getAjaxDataFromForm = function (selector) {
    var ary = $(selector).serializeArray();
    var obj = {};
    for (var a = 0; a < ary.length; a++) {
        var name = ary[a].name;
        if (name.indexOf("[]") == name.length - 2) {
            name = name.substr(0, name.length - 2);
            if (typeof obj[name] == 'undefined') {
                obj[name] = [];
            }
            obj[name].push(ary[a].value);
        } else {
            obj[name] = ary[a].value;
        }
    }
    return obj;
}

Giraffe.prototype.getAjaxUploadDataFromForm = function (selector) {
    var form = $(selector);
    var formdata = new FormData(form[0]);
    return formdata;
}

Giraffe.prototype.dataTableAjax = function (controller, method, data, options, selector, afterInitCallback, afterReloadCallback, drawCallback) {
    if (typeof($.fn.DataTable) !== 'function') {
        alert('DataTables Not Loaded!');
        return false;
    }
    var self = this;

    afterInitCallback = afterInitCallback || function () {
        };
    afterReloadCallback = afterReloadCallback || function () {
        };
    drawCallback = drawCallback || function () {
        };
    data = data || {};
    options = options || {};

    var finalOptions = {
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: self.getAjaxUrl(),
            data: function (d) {
                var returnData = {
                    controller: controller,
                    method: method,
                    data: d
                };
                window.$.extend(returnData.data, data);
                return JSON.stringify(returnData);
            },
            dataSrc: function (d) {

                var postAjax = self.postAjaxFunction(d, controller, method, data);
                self.parseResponse(d);
                if (postAjax) {
                    afterReloadCallback(d.response);
                }

                d.draw = d.response.draw;
                d.recordsTotal = d.response.recordsTotal;
                d.recordsFiltered = d.response.recordsFiltered;

                return d.response.data;
            }
        },
        drawCallback: function (a) {
            drawCallback(a);
        },
        displayStart: 0,
        pageLength: 25,
        stateSave: true,
        stateSaveCallback: function (settings, d) {
            localStorage.setItem(selector + '_DataTables_' + settings.sInstance, JSON.stringify(d))
        },
        stateLoadCallback: function (settings) {
            return JSON.parse(localStorage.getItem(selector + '_DataTables_' + settings.sInstance))
        }

    };

    $.extend(finalOptions, options);

    var table = $(selector).DataTable(finalOptions);
    var page = self.getGetVar('dtPage') || 1;
    page = parseInt(page) - 1;
    if (page) {
        setTimeout(function () {
            table.page(page).draw(false);
        }, 10);
    }

    this.dataTableSelector = selector;
    afterInitCallback();
}
/**
 *
 * @param str
 * @returns {string}
 */
Giraffe.prototype.ucwords = function (str) {
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}
/**
 *
 * @param length
 * @param charset
 * @returns {string}
 */
Giraffe.prototype.stringGen = function (length, charset) {
    var text = "";
    charset = charset || "abcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < length; i++)
        text += charset.charAt(Math.floor(Math.random() * charset.length));
    return text;
}
/**
 *
 * @param name
 * @param value
 * @param hours
 * @param path
 */
Giraffe.prototype.setCookie = function (name, value, hours, path) {
    var path = path || '/';
    var d = new Date();
    d.setTime(d.getTime() + (hours * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + "; " + expires + "; path=" + path;
}
/**
 *
 * @param name
 * @returns {*}
 */
Giraffe.prototype.getCookie = function (name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}
/**
 *
 * @param excludeRegExp
 * @returns {*}
 */
Giraffe.prototype.getGetVars = function (excludeRegExp) {
    excludeRegExp = excludeRegExp || false;
    var self = this;
    if (excludeRegExp) {
        var newGet = {};
        Object.keys(self.$_GET).forEach(function (key) {
            var value = self.$_GET[key];
            if (!value.match(excludeRegExp)) {
                newGet[key] = value;
            }
        });
        return newGet;
    }

    return self.$_GET;
}
/**
 *
 * @param index
 * @returns {boolean}
 */
Giraffe.prototype.getGetVar = function (index) {
    var getVars = this.getGetVars();

    if (this.isset(getVars[index])) {
        return getVars[index];
    }
    return false;
}
/**
 *
 * @param str
 * @param removeRegExp
 * @returns {string}
 */
Giraffe.prototype.encodeURIComponent = function (str, removeRegExp) {
    removeRegExp = removeRegExp || false;
    if (removeRegExp) {
        str = str.replace(removeRegExp, '');
    }
    return encodeURIComponent(str).replace(/[!'()*]/g, function (c) {
        return '%' + c.charCodeAt(0).toString(16);
    });
}

Giraffe.prototype.jsonEncode = function (obj) {
    var json = JSON.stringify(obj, function (k, v) {
        //special treatment for function types
        if (typeof v === "function") {
            return v.toString();//we save the function as string
        }
        return v;
    });

    return json;
}
/**
 *
 * @param json
 */
Giraffe.prototype.jsonDecode = function (json) {
    var self = this;
    var obj = JSON.parse(json, function (k, v) {
        // there is probably a better way to determ if a value is a function string
        if (typeof v === "string" && v.indexOf("function") !== -1)
            return self.compileFunction(v);
        return v;
    });

    return obj;
}
/**
 *
 * @param str
 * @returns {*}
 */
Giraffe.prototype.compileFunction = function (str) {
    //find parameters
    var pstart = str.indexOf('('), pend = str.indexOf(')');
    var params = str.substring(pstart + 1, pend);
    params = params.trim();

    //find function body
    var bstart = str.indexOf('{'), bend = str.lastIndexOf('}');
    var str = str.substring(bstart + 1, bend);

    return Function(params, str);
}

Giraffe.prototype.isFunction = function (fn) {
    if (this.isset(fn) && typeof(fn) === 'function') {
        return true;
    }
    return false;
}

Giraffe.prototype.setAddressBar = function (path, title, stateObj) {
    stateObj = stateObj || null;
    title = title || document.title;
    history.pushState(stateObj, title, path);
}

Giraffe.prototype.addressBarStateHandler = function (callback) {
    var self = this;
    callback = callback || function (stateObj) {
            self.log(stateObj);
        };
    var self = this;
    window.addEventListener('popstate', function (e) {

        var stateObj = e.state;
        try {
            callback(stateObj, self);
        } catch (e) {

        }
    });
}


/**THIS HAS TO BE LAST DECLARED FUNCTION**/
Giraffe.prototype.log = function () {
    var self = window.$G || new Giraffe({}, true);
    var doLogging = (self.environment != 'production' || self.getGetVar('console_log'));
    if (doLogging) {
        return Function.prototype.bind.call(console.log, console);
    } else {
        return function () {
        };
    }
}();