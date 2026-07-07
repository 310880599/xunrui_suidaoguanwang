// (function flexible(window, document) {
//     var docEl = document.documentElement
//     var dpr = window.devicePixelRatio || 1 // 当前显示设备的物理像素分辨率与 CSS 像素分辨率的比率

//     // adjust body font size
//     function setBodyFontSize() {
//         if (document.body) {
//             document.body.style.fontSize = (12 * dpr) + 'px'
//         }
//         else {
//             // 等页面的元素加载完之后再调用一下这个函数。因为浏览器执行代码是从上到下，当解析到script标签的时候，可能body还没构建好。所以需要考虑全面。
//             document.addEventListener('DOMContentLoaded', setBodyFontSize)
//         }
//     }
//     setBodyFontSize();

//     // set 1rem = viewWidth / 10
//     // 设置 html 元素的文字大小，因为 rem 是相对于根节点而定的
//     function setRemUnit() {
//         var rem = docEl.clientWidth / 10 // 除以10是为了方便计算
//         docEl.style.fontSize = rem + 'px' // 将html元素的fontsize设置为10分之1像素
//     }

//     setRemUnit()

//     // reset rem unit on page resize
//     // resize事件是 当页面尺寸大小发生变化的时候,要重新设置rem的大小
//     window.addEventListener('resize', setRemUnit)
//     // pageshow事件是 重新加载页面触发的事件
//     window.addEventListener('pageshow', function (e) {
//         // 如果是从缓存取过来的页面也重新设置一下rem的大小(主要是火狐浏览器有往返缓存机制，这个缓存保存了DOM和JS的状态)
//         if (e.persisted) {
//             setRemUnit()
//         }
//     })

//     // detect 0.5px supports
//     // 有些移动端的浏览器不支持0.5像素的写法
//     if (dpr >= 2) {
//         var fakeBody = document.createElement('body')
//         var testElement = document.createElement('div')
//         testElement.style.border = '.5px solid transparent'
//         fakeBody.appendChild(testElement)
//         docEl.appendChild(fakeBody)
//         if (testElement.offsetHeight === 1) {
//             docEl.classList.add('hairlines')
//         }
//         docEl.removeChild(fakeBody)
//     }
// }(window, document))

function browserRedirect() {
    var sUserAgent = navigator.userAgent.toLowerCase();
    if (/ipad|iphone|midp|rv:1.2.3.4|ucweb|android|windows ce|windows mobile/.test(sUserAgent)) {
        (function (doc, win) {
            var docEl = doc.documentElement,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                recalc = function () {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth) return;
                    if (clientWidth >= 750) {
                        docEl.style.fontSize = '100px';
                    } else {
                        docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
                    }
                };

            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
        })(document, window);

        console.log("移动");
    } else {
        //跳转pc端页面
        (function (win) {
            var tid;
            function refreshRem() {
                let designSize = 1920; // 设计图尺寸
                let html = document.documentElement;
                let wW = html.clientWidth; // 窗口宽度
                let rem = wW * 100 / designSize;
                document.documentElement.style.fontSize = rem + 'px';
            }
            win.addEventListener('resize', function () {
                clearTimeout(tid);
                tid = setTimeout(refreshRem, 1);
            }, false);
            win.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    clearTimeout(tid);
                    tid = setTimeout(refreshRem, 1);
                }
            }, false);
            refreshRem();
        })(window);;
        console.log("PC");
    }
}

browserRedirect();


$(window).resize(function () {

    browserRedirect();
});