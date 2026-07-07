
/**
 * lib-flexible 0.3.2经典版
 * 目前都是使用这个版本开发
 * 
 * 大部分页面 meta name="viewport" 的配置
 * <meta name="viewport" content="width=device-width, viewport-fit=cover, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
 * 
 * 添加注释 @lizhiyu
 * 时间 2021-5-13
 */
(function (win, lib) {
    var doc = win.document;
    var docEl = doc.documentElement;
    var metaEl = doc.querySelector('meta[name="viewport"]');
    var flexibleEl = doc.querySelector('meta[name="flexible"]');
    var dpr = 0;
    var scale = 0;
    var tid;
    var flexible = lib.flexible || (lib.flexible = {});

    // 如果有设置缩放比例，那么按照设置的比例显示
    if (metaEl) {
        console.warn('将根据已有的meta标签来设置缩放比例');
        var match = metaEl.getAttribute('content').match(/initial\-scale=([\d\.]+)/);
        if (match) {
            scale = parseFloat(match[1]);
            dpr = parseInt(1 / scale);
        }
    } else if (flexibleEl) {
        var content = flexibleEl.getAttribute('content');
        if (content) {
            var initialDpr = content.match(/initial\-dpr=([\d\.]+)/);
            var maximumDpr = content.match(/maximum\-dpr=([\d\.]+)/);
            if (initialDpr) {
                dpr = parseFloat(initialDpr[1]);
                scale = parseFloat((1 / dpr).toFixed(2));
            }
            if (maximumDpr) {
                dpr = parseFloat(maximumDpr[1]);
                scale = parseFloat((1 / dpr).toFixed(2));
            }
        }
    }
    // 默认的缩放比例
    if (!dpr && !scale) {
        var isAndroid = win.navigator.appVersion.match(/android/gi);
        var isIPhone = win.navigator.appVersion.match(/iphone/gi);
        var devicePixelRatio = win.devicePixelRatio;
        if (isIPhone) {
            // iOS下，对于2和3的屏，用2倍的方案，其余的用1倍方案
            if (devicePixelRatio >= 3 && (!dpr || dpr >= 3)) {
                dpr = 3;
            } else if (devicePixelRatio >= 2 && (!dpr || dpr >= 2)) {
                dpr = 2;
            } else {
                dpr = 1;
            }
        } else {
            // 其他设备下，仍旧使用1倍的方案
            dpr = 1;
        }
        scale = 1 / dpr;
    }

    docEl.setAttribute('data-dpr', dpr); // html根标签上获取
    // 没有设置<meta name="viewport">的会默认添加一个
    if (!metaEl) {
        metaEl = doc.createElement('meta');
        metaEl.setAttribute('name', 'viewport');
        metaEl.setAttribute('content', 'initial-scale=' + scale + ', maximum-scale=' + scale + ', minimum-scale=' + scale + ', user-scalable=no');
        if (docEl.firstElementChild) { // 添加到head
            docEl.firstElementChild.appendChild(metaEl);
        } else {
            var wrap = doc.createElement('div');
            wrap.appendChild(metaEl);
            doc.write(wrap.innerHTML);
        }
    }

    function refreshRem() {
        var width = docEl.getBoundingClientRect().width; // getBoundingClientRect可以获取页面的宽高
        if (width / dpr > 540) {
            width = 540 * dpr; // 默认的适配最大宽度界限
        }
        var rem = width / 10; // 方便计算除以10
        docEl.style.fontSize = rem + 'px'; // 设置根元素的fontsize，rem是基于根元素的fontsize来设定的
        flexible.rem = win.rem = rem;
    }

    // 监听页面，窗口或框架被调整大小时修改页面的fontsize
    win.addEventListener('resize', function () {
        clearTimeout(tid);
        tid = setTimeout(refreshRem, 300);
    }, false);
    // pageshow事件是 重新加载页面触发的事件
    win.addEventListener('pageshow', function (e) {
        // 如果是从缓存取过来的页面也重新设置一下rem的大小(主要是火狐浏览器有往返缓存机制，这个缓存保存了DOM和JS的状态)
        if (e.persisted) {
            clearTimeout(tid);
            tid = setTimeout(refreshRem, 300);
        }
    }, false);

    if (doc.readyState === 'complete') { // 文档加载完成
        doc.body.style.fontSize = 12 * dpr + 'px'; // 设置body里文字的基本fontsize
    } else {
        doc.addEventListener('DOMContentLoaded', function (e) { // 监听加载完成后调用
            doc.body.style.fontSize = 12 * dpr + 'px';
        }, false);
    }


    refreshRem();

    // 提供的一些数据和方法
    flexible.dpr = win.dpr = dpr;
    flexible.refreshRem = refreshRem;
    flexible.rem2px = function (d) {
        var val = parseFloat(d) * this.rem; // 根据rem计算出px值
        if (typeof d === 'string' && d.match(/rem$/)) {
            val += 'px';
        }
        return val;
    }
    flexible.px2rem = function (d) {
        var val = parseFloat(d) / this.rem; // 根据px计算出rem值
        if (typeof d === 'string' && d.match(/px$/)) {
            val += 'rem';
        }
        return val;
    }

})(window, window['lib'] || (window['lib'] = {}));

作者：乐生
链接：https://juejin.cn/post/6961658865876205576
来源：稀土掘金
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。