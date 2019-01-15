/**
 * @filename ImageCompresser
 * @description
 * 作者: jinhuiguo(jinhuiguo@tencent.com)
 * 创建时间: 2014-08-6 09:06:03
 * 修改记录:
 *
 * $Id$
 **/

define('module/imageCompresser', ['module/jpegMeta', 'module/JPEGEncoder'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var jpegMeta = require('module/jpegMeta').JpegMeta;
    var JPEGEncoder = require('module/JPEGEncoder');
    module.exports = {
        init: function(){
            /**
             * 图片压缩静态类 from qzone
             */
            var ImageCompresser = {
                /**
                 * 检测ios平台是否有做过抽样处理
                 * @param {Element} img
                 */
                isIosSubSample: function(img){
                    var w = img.naturalWidth,
                        h = img.naturalHeight,
                        hasSubSample = false;
                    if (w * h > 1024 * 1024) { //超过1K*1K分辨率的图会被抽样
                        var canvas = document.createElement('canvas');
                            ctx = canvas.getContext('2d'),
                        canvas.width = canvas.height = 1;
                        ctx.drawImage(img, 1 - w, 0);
                        hasSubSample = ctx.getImageData(0, 0, 1, 1).data[3] === 0;
                        canvas = ctx = null;//清理
                    }
                    return hasSubSample;
                },

                /**
                 * 获取ios上图片被压缩比例
                    * 随机使用1px宽度尝试绘制样图，估算原图被ios压缩了多少
                 * @param {Element} img
                 * @param {Number} w
                 * @param {Number} h
                 */
                getIosImageRatio: function(img, w, h){
                    var canvas = document.createElement('canvas'),
                        ctx = canvas.getContext('2d'),
                        data,
                        sy = 0, //起始y坐标
                        ey = h, //结束y坐标
                        py = h; //当前判断的y坐标
                    canvas.width = 1;
                    canvas.height = h
                    ctx.drawImage(img, 1 - Math.ceil(Math.random() * w), 0); //随机画1px宽度
                    data = ctx.getImageData(0, 0, 1, h).data;
                    while (py > sy) {
                        var alpha = data[(py - 1) * 4 + 3];//Notice:如果原图自带透明度，这里可能会失效
                        if (alpha === 0) {
                            ey = py;
                        }
                        else {
                            sy = py;
                        }
                        py = (ey + sy) >> 1;
                    }
                       return py / h;
                },

                /**
                 * 核心函数：获取图片base64
                 * @param {Elemnt} img
                 * @param {opts} opts
                 *      opts.maxW {Number}  目标图片最大宽度
                 *      opts.maxH {Number}  目标图片最大高度
                 *      quality {Number}    目标图片压缩质量
                 *      orien {Number}      目标图片旋转方向
                 */
                getImageBase64: function(img, opts){
                    //合并默认配置（默认配置在前，如果有定义WIFI配置会覆盖）
                    opts = jQuery.extend({
                        maxW: 800, //目标宽
                        maxH: 800, //目标高
                        quality: 0.8, //目标jpg图片输出质量
                        orien: 1
                    }, opts);
                    //获取配置
                    var maxW = opts.maxW, // 设置需要压缩的宽度，图片最大限制
                        maxH = opts.maxW, // 设置需要压缩的高度
                        quality = opts.quality, // 设置需要压缩的质量
                        _w = img.naturalWidth, 	// 图片实际宽度
                        _h = img.naturalHeight, // 图片实际高度
                        w, h; // 输出图片目标大小
                    // ios平台针对大图做抽值处理
                    if(jq.os.ios && ImageCompresser.isIosSubSample(img)){
                        _w = _w / 2;
                        _h = _h / 2;
                    }
                    // 获取最终生成图片大小
                    if(_w > maxW && _w/_h >= maxW/maxH){
                    	// 图片实际宽度大于要设置的宽度，且实际宽高比大于要设置的宽高比
                        w = maxW; // 取目标宽度
                        h = _h * maxW / _w; // 压缩高度
                    } else if(_h > maxH && _h/_w  >= maxH/maxW){
                    	// 图片实际高度超过期望高度，并宽高比比期望宽高比大
                        w = _w * maxH / _h; // 压缩宽度
                        h = maxH; // 取目标高度
                    } else {
                    	// 等比例情况下取实际宽高
                        w = _w;
                        h = _h;
                    }
                    // 创建canvas临时工具
                    var canvas = document.createElement('canvas'),
                        ctx = canvas.getContext('2d'),
                        base64Str;
                    this.doAutoRotate(canvas, w, h, opts.orien); // 旋转图片
                    // ios平台safari支持canvas，贴瓷砖处理大图片
                    if(jq.os.ios) {
                        var tmpCanvas = document.createElement('canvas'),
                            tmpCtx = tmpCanvas.getContext('2d'),
                            d = 1024, //瓷砖canvas的大小
                            vertSquashRatio = ImageCompresser.getIosImageRatio(img, _w, _h), //ios平台大尺寸图片压缩比
                            sx, sy, sw, sh, dx, dy, dw, dh;
                        tmpCanvas.width = tmpCanvas.height = d;
                        sy = 0;
                        while (sy < _h) {
                            sh = sy + d > _h ? _h - sy : d,
                            sx = 0;
                            while (sx < _w) {
                                sw = sx + d > _w ? _w - sx : d;
                                tmpCtx.clearRect(0, 0, d, d);
                                tmpCtx.drawImage(img, -sx, -sy);
                                dx = Math.floor(sx * w / _w);
                                dw = Math.ceil(sw * w / _w);
                                dy = Math.floor(sy * h / _h / vertSquashRatio);
                                dh = Math.ceil(sh * h / _h / vertSquashRatio);
                                ctx.drawImage(tmpCanvas, 0, 0, sw, sh, dx, dy, dw, dh);
                                sx += d;
                            }
                            sy += d;
                        }
                        tmpCanvas = tmpCtx = null;
                    }
                    else {
                        ctx.drawImage(img, 0, 0, _w, _h, 0, 0, w, h);
                    }
                    // android平台调用jpegEncoder处理生成jpg压缩格式
                    if (jq.os.android) {
                        var imgData = ctx.getImageData(0, 0, canvas.width, canvas.height), // 用canvas创建一个图片对象imgData
                            encoder = new JPEGEncoder.JPEGEncoder(quality * 100);
                        base64Str = encoder.encode(imgData); // imgData对象用encoder进行压缩，默认质量
                        encoder = null;
                    }
                    else {
                        base64Str = canvas.toDataURL('image/jpeg', quality); // IOS下用canvas的toDataURL方法
                    }

                    //console.log(base64Str);
                    canvas = ctx = null;
                    //console.log("image base64Str:"+base64Str);
                    return base64Str;
                },
                /**
                 * 旋转图片
                 * @param {Element} canvas 画布
                 * @param {Number} width 宽度
                 * @param {Number} height 高度
                 * @param {Number} orientation 旋转角度[1|2|3|4|5|6|7|8]
                 */
                doAutoRotate: function(canvas, width, height, orientation) {
                    var ctx = canvas.getContext('2d');
                    if (orientation >= 5 && orientation <= 8) {
                        canvas.width = height;
                        canvas.height = width;
                    }
                    else {
                        canvas.width = width;
                        canvas.height = height;
                    }
                    switch (orientation) {
                        case 2:
                            // horizontal flip
                            ctx.translate(width, 0);
                            ctx.scale(-1, 1);
                            break;
                        case 3:
                            // 180 rotate left
                            ctx.translate(width, height);
                            ctx.rotate(Math.PI);
                            break;
                        case 4:
                            // vertical flip
                            ctx.translate(0, height);
                            ctx.scale(1, -1);
                            break;
                        case 5:
                            // vertical flip + 90 rotate right
                            ctx.rotate(0.5 * Math.PI);
                            ctx.scale(1, -1);
                            break;
                        case 6:
                            // 90 rotate right
                            ctx.rotate(0.5 * Math.PI);
                            ctx.translate(0, -height);
                            break;
                        case 7:
                            // horizontal flip + 90 rotate right
                            ctx.rotate(0.5 * Math.PI);
                            ctx.translate(width, -height);
                            ctx.scale(-1, 1);
                            break;
                        case 8:
                            // 90 rotate left
                            ctx.rotate(-0.5 * Math.PI);
                            ctx.translate(-width, 0);
                            break;
                        default:
                            break;
                    }
                },
                /**
                 * 获取文件地址
                 * @param {File} file
                 */
                getFileObjectURL: function(file){
                    var URL = window.URL || window.webkitURL || false;
                    if (URL) {
                        return URL.createObjectURL(file);
                    }
                },

                /**
                 * 检测是否支持本地压缩。
                 */
                support: function(){
                    return typeof(window.File) && typeof(window.FileList) && typeof(window.FileReader) && typeof(window.Blob);
                }
            };
            this.ImageCompresser = ImageCompresser; // 把在当前函数中定义的局部对象给到整个模块的成员对象
        }
    }
    module.exports.init();
});