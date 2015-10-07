
function baseFun() {
    this.id = function (e) {
        return document.getElementById(e)
    }, this.tag = function (e) {
        var t = e.split(" ");
        return this.id(t[0]).getElementsByTagName(t[1])
    }, this.toggle = function (e) {
        this.id(e).style.display = "none" == this.id(e).style.display || "" == this.id(e).style.display ? "block" : "none"
    }, this.clone = function (e) {
        var t, a, o = e;
        if (e && ((a = e instanceof Array) || e instanceof Object)) {
            o = a ? [] : {};
            for (t in e)e.hasOwnProperty(t) && (o[t] = this.clone(e[t]))
        }
        return o
    }, this.extend = function (e, t, a) {
        if (t instanceof Array)for (var o = 0, n = t.length; n > o; o++)this.extend(e, t[o], a);
        for (var o in t)o in e && (e[o] = t[o]);
        return e
    }, this.obj2str = function (e) {
        var t = [], a = arguments.callee;
        if ("string" == typeof e)return '"' + e.replace(/([\'\"\\])/g, "\\$1").replace(/(\n)/g, "\\n").replace(/(\r)/g, "\\r").replace(/(\t)/g, "\\t") + '"';
        if ("undefined" == typeof e)return "undefined";
        if ("object" == typeof e) {
            if (null === e)return "null";
            if (e.sort) {
                for (var o = 0; o < e.length; o++);
                t.push(a(e[o])), t = "[" + t.join() + "]"
            } else {
                for (var o in e)t.push('"' + o + '":' + a(e[o]));
                t = "{" + t.join() + "}"
            }
            return t
        }
        return e.toString()
    }, this.addClass = function (e, t) {
        e.className.match(t) || (e.className = e.className + " " + t)
    }, this.removeClass = function (e, t) {
        e.className = e.className.replace(t, "")
    }, this.addHandler = function (e, t, a) {
        e.addEventListener ? e.addEventListener(t, a, false) : e.attachEvent && e.attachEvent("on" + t, a)
    }, this.removeEvt = function (e, t, a) {
        e.removeEventListener ? e.removeEventListener(t, a, false) : e.detachEvent && e.detachEvent("on" + t, a)
    }
}

function showError(e) {
    var t;
    t = e ? language[opt.language]._errorNetwork : language[opt.language]._errorLOADING, t = t.replace("%TOKEN", opt.token), _.id(opt.renderTo).innerHTML = '<div class="errloading">' + t + "</div>"
}

function getOffset(e) {
    var t = e.target;
    void 0 == t.offsetLeft && (t = t.parentNode);
    var a = getPageCoord(t), o = {
        x: window.pageXOffset + e.clientX,
        y: window.pageYOffset + e.clientY
    }, n = {offsetX: o.x - a.x, offsetY: o.y - a.y};
    return n
}

function getPageCoord(e) {
    for (var t = {x: 0, y: 0}; e;)t.x += e.offsetLeft, t.y += e.offsetTop, e = e.offsetParent;
    return t
}

try {
    var MAX_ERR_TIME = 3,
        gErrTimes = 0,
        noCaptcha = new Function,
        TEXTELEM = new Object,
        opt = new Object,
        _ = new baseFun,
        scale_btn = "_n1z",
        scale_bar = "_n1t",
        language = {
            cn: {
                _startTEXT: "请按住滑块，拖动到最右边",
                _yesTEXT: "<b>验证通过</b>",
                _noTEXT: "请在下方输入验证码",
                _errorTEXT: "验证码输入错误，请重新输入",
                _errorClickTEXT: "验证码点击错误，请重试",
                _errorLOADING: '加载失败，请<a href="javascript:__nc.reset()">点击刷新</a>',
                _errorTooMuch: '验证码输入错误。请重新输入',
                _Loading: "<b>加载中</b>",
                _errorServer: "服务器错误或者超时",
                _error300: '哎呀，出错了，点击<a href="javascript:__nc.reset()">刷新</a>再来一次',
                _errorNetwork: '网络不给力，请<a href="javascript:__nc.reset()">点击刷新</a>',
                _submit: "提交"
            },
            en: {
                _startTEXT: "Please slide to verify",
                _yesTEXT: "<b>Verified</b>",
                _noTEXT: "Please input verification code",
                _errorTEXT: "Please try again",
                _errorClickTEXT: "Please try again",
                _errorLOADING: 'Loading failed, <a href="javascript:__nc.reset()">refresh</a>',
                _errorTooMuch: 'Please try again',
                _errorServer: "Server Error",
                _Loading: "<b>Loading</b>",
                _error300: 'Oops... something\'s wrong. Please <a href="javascript:__nc.reset()">refresh</a> and try again.',
                _errorNetwork: 'Net Err. Please <a href="javascript:__nc.reset()">refresh</a>.',
                _submit: "Submit",
            }
        }, default_opt = {
            renderTo: "",
            isEnabled: true,
            foreign: 0,
            appkey: "",
            trans: "",
            token: "",
            elementID: "",
            timeout: 3000,
            language: "cn",
            callback: function () {
            },
            error: function () {
            }
        }, URL_MAP = {
            0: {
                analyze: analyze_url,
                get_captcha: get_captcha,
                get_img: get_image,
                checkcode: check_code
            }
        }, ajaxURL;

    noCaptcha.prototype = {
        init: function (e) {
            window.__nc = this,
            e.foreign && (default_opt.language = "en"),
                opt = _.extend(_.clone(default_opt), e),
                ajaxURL = URL_MAP[opt.foreign] || URL_MAP[0],
            opt.renderTo && opt.appkey && opt.token
            && (_.id(opt.renderTo).innerHTML = '' +
                '<div id="nocaptcha">' +
                '<div id="_n1t_loading" class="nc_scale">' +
                '<div id="_bg" class=" " style="width: 0px;"></div>' +
                '<div id="_scale_text_loading" class="scale_text">' + language[opt.language]._Loading + "</div>" +
                "</div>" +
                "</div>",
                this.reload()
            )
        }, reload: function () {
            _.id(opt.renderTo).innerHTML = tpl,
                TEXTELEM = _.tag(scale_bar + " div")[1],
            opt.isEnabled && new scale(scale_btn, scale_bar, this)
        }, reset: function () {
            opt.renderTo && opt.appkey && opt.token && (_.id(opt.renderTo).innerHTML = '' +
                '<div id="nocaptcha">' +
                '<div id="_n1t_loading" class="nc_scale">' +
                '<div id="_bg" class=" " style="width: 0px;"></div>' +
                '<div id="_scale_text_loading" class="scale_text">' + language[opt.language]._Loading + "</div>" +
                "</div>" +
                "</div>",
                this.reload()
            )
        }, enabled: function () {
            return new scale(scale_btn, scale_bar, this)
        }, errorCallback: function (e) {
            var t = _.id(scale_bar),
                a = this,
                o = t.getElementsByTagName("span")[0],
                n = t.getElementsByTagName("div")[0];
                showError(e),
                _.addClass(n, "orange"),
                _.addClass(o, "reload"),
                _.addHandler(t, "click", function () {
                    a.reload(),
                    _.removeEvt(t, "click")
                }),
                e && opt.error(language[opt.language]._errorServer)
        }, onScaleReady: function (elem) {
            var trans = {};
            opt.trans && (trans = eval("0," + opt.trans));
            for (var arr = opt.elementID || [], i = 0; i < arr.length; i++) {
                var id = arr[i],
                    el = document.getElementById(id);
                el && (trans[id] = el.value)
            }
            var me = this;
            TEXTELEM.innerHTML = language[opt.language]._Loading,
                _.addClass(TEXTELEM, "scale_text2"),
                $.ajax({
                    type: "post",
                    url: ajaxURL.analyze,
                    data: {a: opt.appkey, t: opt.token, lang: opt.language},
                    dataType: "json",
                    success: function(e){
                        me.onScaleReadyCallback(e, elem)
                    },
                    error: function(){
                        console.log('onScaleReady'),
                            me.errorCallback(e)
                    }
                });
        }, onScaleReadyCallback: function (e, t) {
            if(200 == e.code){
                var a= e.data,o= a.code;
                if(0 == o){
                    _.id(scale_btn).className = "btnok",
                        TEXTELEM.innerHTML = language[opt.language]._yesTEXT,
                        this.userCallback(a.csessionid, "pass", a.value)
                }else{
                    _.addClass(t.btn, "btnok2");
                    t.bar = _.tag(scale_bar + " div")[0];
                    TEXTELEM.innerHTML = language[opt.language]._Loading;
                    if(100==o){
                        this.onScale100(a.csessionid, a.value);
                    }else if(200==o){
                        this.onScale200(a.csessionid, a.value)
                    }else{
                        _.id(opt.renderTo).innerHTML = '<div class="errloading">' + language[opt.language]._error300 + "</div>";
                    }
                }
            }else{
                this.errorCallback()
            }
        }, onScale100: function (e, t, a) {
            var o = arguments.callee, n = a || this,
                i = _.tag("clickCaptcha div");
            o.caller == this.onScaleReadyCallback && _.addHandler(_.id("_btn_2"), "click", function () {
                o(e, t)
            });
            var r, c = setTimeout(function () {
                c = -1, r || (showError(true), console.log('captcha timeout'))
            }, 5000);

            $.ajax({
                type: "post",
                url: ajaxURL.get_img,
                data: {a: opt.appkey, t: opt.token},
                dataType: "json",
                success: function(a){
                    if (r = true, -1 != c) {
                        if (clearTimeout(c), !a.tag)
                            return console.log('no tag'), void showError(true);
                        _.id("clickCaptcha").style.display = "block";
                        var d = a.questiontext.split(a.tag[0]);
                        _.id("_scale_text").innerHTML = d[0] + "<i>“" + a.tag[0] + "”</i>" + d[1],
                        i[1].innerHTML = '<img src="' + path+a.imgurl + '?'+Math.random()+'" >',
                        imgElem = i[1].getElementsByTagName("img")[0];
                        _.addHandler(imgElem, "click", function (a) {
                            TEXTELEM.innerHTML = language[opt.language]._Loading,
                                $.ajax({
                                    type: "post",
                                    url: ajaxURL.checkcode,
                                    dataType: "json",
                                    data: {
                                        ctype: 'noCaptcha',//成语验证码
                                        csessionid: e,
                                        checkcode: function () {
                                            var e = {};
                                            return e.w = imgElem.width.toString(),
                                                e.h = imgElem.height.toString(),
                                                e.x = void 0 == a.offsetX ? getOffset(a).offsetX : a.offsetX,
                                                e.y = void 0 == a.offsetY ? getOffset(a).offsetY : a.offsetY,
                                                e.x = parseInt(e.x).toString(),
                                                e.y = parseInt(e.y).toString(),
                                                _.obj2str(e)
                                        }(),
                                        appkey: opt.appkey,
                                        token: opt.token
                                    },
                                    success: function(a){
                                        if (200 == a.code){
                                            _.id(scale_btn).className = "btnok";
                                            _.tag(scale_bar + " div")[0].className = "";
                                            TEXTELEM.innerHTML = language[opt.language]._yesTEXT;
                                            _.toggle("clickCaptcha");
                                            n.userCallback && n.userCallback(e, t, a.data.sig);
                                        }else if (201 == a.code) {
                                            o(e, t, n);
                                            var i = _.id("_captcha_text"),
                                                r = language[opt.language]._errorClickTEXT;
                                            ++gErrTimes > MAX_ERR_TIME && (r = language[opt.language]._errorTooMuch.replace("%TOKEN", opt.token)),
                                                i.innerHTML = r, i.style.visibility = "visible"
                                        } else {
                                            _.id(opt.renderTo).innerHTML = '<div class="errloading">' + language[opt.language]._error300 + "</div>";
                                        }
                                    },
                                    error: function(){
                                        n.errorCallback(e)
                                    }
                                });
                        })
                    }
                },
                error: function(){
                    _.id(opt.renderTo).innerHTML = '<div class="errloading">' + language[opt.language]._errorLOADING + "</div>", n.errorCallback(true)
                }
            });
        }, onScale200: function (e, t) {
            _.id("imgCaptcha").style.display = "block";
            var a = arguments.callee,
                o = this,
                n = _.tag("imgCaptcha div"),imgElem;
                TEXTELEM.innerHTML = language[opt.language]._noTEXT;
                _.id("scale_submit").innerHTML = language[opt.language]._submit;
                $.ajax({
                    url: get_captcha,
                    success: function (data) {
                        n[1].innerHTML = '<img src="' + path+data+'?'+Math.random() + '" >';
                        imgElem = n[1].getElementsByTagName("img")[0];
                        _.addHandler(imgElem, "click", function () {
                            a(e, t)
                        });
                    }
                });
                arguments.callee.caller === this.onScaleReadyCallback && (_.addHandler(_.id("scale_submit"), "click", function () {
                    $.ajax({
                        type:'post',
                        url: ajaxURL.checkcode,
                        data: {
                            ctype: 'Captcha',//普通验证码
                            csessionid: e,
                            checkcode: function () {
                                var e = {};
                                e.code = _.tag("imgCaptcha input")[0].value;
                                return _.obj2str(e)
                            }(),
                            appkey: opt.appkey,
                            token: opt.token
                        },
                        success: function (n) {
                            var i = _.tag("imgCaptcha div")[2];
                            if (200 == n.code){
                                _.id(scale_btn).className = "btnok";
                                _.tag(scale_bar + " div")[0].className = "";
                                TEXTELEM.innerHTML = language[opt.language]._yesTEXT;
                                i.style.borderTopColor = "#e5e5e5";
                                _.toggle("imgCaptcha");
                                o.userCallback.call(this, e, t, n.data.sig);
                            }else if (201 == n.code) {
                                a(e, t);
                                var r = language[opt.language]._errorTEXT;
                                ++gErrTimes > MAX_ERR_TIME && (r = language[opt.language]._errorTooMuch.replace("%TOKEN", opt.token));
                                var c = _.id("_captcha_img_text");
                                c.innerHTML = r,
                                c.style.display = "block",
                                i.style.borderTopColor = "red"
                            } else{
                                _.id(opt.renderTo).innerHTML = '<div class="errloading">' + language[opt.language]._error300 + "</div>";
                            }
                        },
                        error: function(){
                            o.errorCallback(e)
                        }
                    });
                }),
                _.addHandler(_.id("_btn_2"), "click", function () {
                    a(e, t)
                })
            )
        }, userCallback: function (e, t, a) {
            var o = {csessionid: e || null, value: t || null, sig: a || null};
            opt.callback.call(this, o)
        }
    };

    var scale = function (e, t, a) {
        TEXTELEM.innerHTML = language[opt.language]._startTEXT,
            this.btn = document.getElementById(e),
            this.bar = document.getElementById(t),
            this.step = this.bar.getElementsByTagName("DIV")[0],
            this.init(a)
    };
    scale.prototype = {
        init: function (e) {
            function t(t) {
                function c(t) {
                    var c = (t || i.event).clientX,
                        s = r.min(g, r.max(-2, p + (c - u)));
                    if (o.btn.style.left = s + "px", o.ondrag(r.round(100 * r.max(0, s / g)), s), s == g) {
                        o.btn.onmousedown = null,
                            n.onmousemove = null,
                            n.onmouseup = null,
                            _.removeEvt(n, "touchmove", l),
                            _.removeEvt(n, "touchend", d),
                            _.removeEvt(o.btn, "touchstart", a);
                        var m = {};
                        m.btn = o.btn,
                            m.bar = o.bar.childNodes[1],
                            e.onScaleReady(m)
                    }
                }

                function s(e) {
                    var t = parseInt(o.btn.style.left);
                    g > t && (_.addClass(o.btn, "button_move"),
                        _.addClass(_.id("_bg"), "bg_move"),
                        o.btn.style.left = "0px",
                        o.ondrag(0, 0),
                        setTimeout(function () {
                            _.removeClass(o.btn, "button_move"),
                            _.removeClass(_.id("_bg"), "bg_move")}, 500)),
                        this.onmousemove = null,
                        _.removeEvt(this, "touchmove", l)
                }

                function d(e) {
                    s.call(this, e.touches[0])
                }

                function l(e) {
                    e.preventDefault(),
                        c.call(this, e.touches[0])
                }

                var u = (t || i.event).clientX,
                    p = this.offsetLeft,
                    g = o.bar.offsetWidth - this.offsetWidth;
                n.onmousemove = c,
                    n.onmouseup = s,
                    _.addHandler(n, "touchmove", l),
                    _.addHandler(n, "touchend", d);

            }

            function a(e) {
                e.preventDefault(), t.call(this, e.touches[0])
            }

            var o = this, n = document, i = window, r = Math;
            o.btn.onmousedown = t,
                _.addHandler(o.btn, "touchstart", a),
                o.bar.onselectstart = function () {
                return false
            }
        }, ondrag: function (e, t) {
            this.step.style.width = Math.max(0, t) + "px"
        }, text: function () {
        }
    };
    var tpl = '' +
        '<div id="_n1t" class="nc_scale">' +
        '<div id="_bg"></div>' +
        '<span id="_n1z"></span>' +
        '<div id="_scale_text" class="scale_text"></div>' +
        '<div id="clickCaptcha">' +
        '<div class="clickCaptcha_text">' +
        '<b id="_captcha_text"></b>' +
        '<img id="_btn_2" src="../images/btn2.png">' +
        '</div>' +
        '<div class="clickCaptcha_img"></div>' +
        '<div class="clickCaptcha_btn"></div>' +
        '</div>' +
        '<div id="imgCaptcha">' +
        '<div class="imgCaptcha_text">' +
        '<input type="text">' +
        '</div>' +
        '<div class="imgCaptcha_img" id="_imgCaptcha_img"></div>' +
        '<img id="_btn_1" src="../images/btn2.png" onclick="document.getElementById(\'_imgCaptcha_img\').children[0].click()">' +
        '<div class="imgCaptcha_btn">' +
        '<div id="_captcha_img_text"></div>' +
        '<div id="scale_submit"></div>' +
        '</div>' +
        '</div>' +
        '</div>';
    window.noCaptcha = noCaptcha;
    var fnShowHelp
} catch (e) {
    console.log('global ' + e.message),
        console.error('nc.js err:', e)
}
