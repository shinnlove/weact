/**
 * @filename scroll
 * @description
 * 作者: yixizhou(yixizhou@tencent.com)
 * 创建时间: 2014-04-18 09:06:03
 * 修改记录:
 *
 * $Id$
 **/

define('lib/scroll', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports = {
        /**
         * todo 整理代码 写成jquery 插件
         * @param object opts
         *      - string cSelector 事件绑定容器筛选器
         *      - string ulSelector ul筛选器
         *      - boolean isFlip 是否为翻页
         *      - string pageOnClass 当前分页样式class
         *      - string align 翻页模式下图片位置 默认 center
         */
        initScroll: function(opts) {
            var cSelector = opts.cSelector || 'body';
            var isFlip = opts.isFlip || false;
            var ulSelector = opts.ulSelector;
            var align = opts.align || 'center';
            var nextBtn = opts.nextBtn || '';
            var preBtn = opts.preBtn || '';
            this.childTag = opts.childTag || 'li';
            this.minX = 0;
            this.tabIndex = 0;
            this.left = 0;
            this.ulObj = '';
            this.pageOnClass = opts.pageOnClass || 'on';

            this.ulObj = jq(jq(ulSelector)[0]);
            this.ulObj.css({'position':'relative'});
            var that = this;

            // 图片横滑
            var x, y, endX, endY, offsetX, offsetY, objLeft;
            var slideDirect = 0;

            // todo 或者改成不动态绑
            jq(cSelector).on('touchstart', ulSelector, function(e) {
                jq(this).css({'position':'relative'});
                x = endX = e.originalEvent.touches[0].pageX;
                y = endY = e.originalEvent.touches[0].pageY;
                that.ulObj = jq(this);
                that.left = parseInt(that.ulObj.attr('scrolleft') || 0);
                objLeft = that.left;
            });
            jq(cSelector).on('touchmove', ulSelector, function(e) {
                // document.ontouchmove = function(e){ e.preventDefault();}
                endX = e.originalEvent.touches[0].pageX;
                endY = e.originalEvent.touches[0].pageY;
                offsetX = endX - x;
                offsetY = endY - y;
                // 图片上竖滑不明显时禁用上下滑
                if (Math.abs(offsetY) < Math.abs(offsetX)) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    //document.ontouchmove = function(e){ e.preventDefault();}
                } else {
                    return true;
                    //document.ontouchmove = function(e){ return true;}
                }
                slideDirect = 0;
                if(offsetX > 20) {
                    slideDirect = 1;
                } else if(offsetX < -20) {
                    slideDirect = -1;
                }
                var obj = jq(this);
                that.left =  objLeft + parseInt(offsetX);
                // 防止左滑过头
                if (that.left > 0) {
                    that.left = 0;
                    offsetX = 0;
                    offsetY = 0;
                }
                if (!isFlip || isFlip && align == 'left') {
                    that.minX = 0;
                    obj.find(that.childTag).each(function(i, e) {
//                        that.minX += jq(e).width();
                        that.minX += jq(e)[0].scrollWidth;
                    });
                    var parentObj = obj.parent();
                    if (that.minX < parentObj.width()) {
                        that.minX = 0;
                    } else {
                        obj.removeClass('slide_c');
                        that.minX = that.minX - parentObj.width() + parentObj.offset().left;
                    }
                    that.minX *= -1;
                } else {
                    var liObj = obj.find(that.childTag);
                    that.minX = -1 * liObj.width() * (liObj.length - 1);
                }
                // 防止左滑过头
                if (that.left <= that.minX) {
                    that.left = that.minX;
                    offsetX = 0;
                    offsetY = 0;
                }
                that.ulObj.attr('scrolleft', that.left);
                jq(this).css("left", that.left);

            });
            jq(cSelector).on('touchend', ulSelector, function(e) {
                if (!isFlip) {
                    objLeft = that.left;
                    that.ulObj.attr('scrolleft', that.left);
                    document.ontouchstart = function(e){ return true;}
                } else {
                    that.changeTab(-1, slideDirect, align);
                    offsetX = 0;
                    slideDirect = 0;
                }
            });

            if (nextBtn) {
                jq(cSelector).on('click', nextBtn, function(e) {
                    that.ulObj = jq(ulSelector + ':visible');
                    that.changeTab(-1, -1, align);
                    return false;
                });
            }
            if (preBtn) {
                jq(cSelector).on('click', preBtn, function(e) {
                    that.ulObj = jq(ulSelector + ':visible');
                    that.changeTab(-1, 1, align);
                    return false;
                });
            }

            /*
            if (isFlip) {
                jq('.expreList .pNumCon a').on('click', function() {
                    var thisObj = jq(this);
                    var ulId = 'exp' + thisObj.parent().attr('id').replace('page', '');
                    changeTab(ulId, thisObj.index());
                });
            }
            */
            this.changeTab = function(index, direct, align) {
                var ulId = this.ulObj.attr('id');
                var currObj = jq('#' + ulId);
                currObj.css({'position':'relative'});
                var pObj = jq('#' + ulId + '_page a');
                var len = pObj.length;

                this.tabIndex = jq('#' + ulId + '_page').attr('curr') || 0;
                this.tabIndex = parseInt(this.tabIndex > len - 1 ? len - 1 : this.tabIndex);
                if(index < 0) {
                    if(direct > 0) {
                        index = this.tabIndex - 1;
                    } else if(direct < 0) {
                        index = this.tabIndex + 1;
                    } else {
                        index = this.tabIndex;
                    }
                }

                if(index > len - 1 || index < 0) {
                    pObj.removeClass(this.pageOnClass);
                    pObj.eq(this.tabIndex).addClass(this.pageOnClass);
                    return;
                }


                var liObj = currObj.find(this.childTag);
                // 这种方式限图片不超过屏宽时体验最好
                if (align == 'left') {
                    var le = 0;
                    liObj.each(function(i, e) {
                        if (i < index) {
                            // todo  补白
                            le += jq(e).outerWidth(true);
                        }
                    });
                    le *= -1;
                    if (le < this.minX) {
                        le = this.minX
                    }
                } else {
                    var pageWidth = liObj.outerWidth();
                    var le = -1 * pageWidth * index;
                }
                var le_px =  le + "px";

                this.left = le;
                currObj.attr('scrolleft', this.left);

                currObj.stop().animate({
                    "left": le_px
                }, 100, function(){
                    // 修改 Left
                    this.left = le;
                    currObj.attr('scrolleft', this.left);
                });

                pObj.removeClass(this.pageOnClass);
                pObj.eq(index).addClass(this.pageOnClass);

                jq('#' + ulId + '_page').attr('curr', index);
                this.tabIndex = index;
            }
        },
        setUlObj: function() {
        }
    };
});

