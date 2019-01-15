/**
 * Created by Administrator on 2016/7/30.
 */
define('module/MLoading', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量

    module.exports = {
        _preventDefault:"javascript:void(0)",
        _notExist:function(checkObj){
            return (typeof checkObj == "undefined")
        },
        _initDefaultDepthOnce:function(){
            if (module.exports._notExist(window._dlgBaseDepth)) {
                window._dlgBaseDepth = 900 // 没有定义对话框深度,默认900
            }
        },
        _createModal:function(){
            var mModalHtml = '<div class="mModal"><a href="' + module.exports._preventDefault + '"></a></div>';
            document.querySelector("body").insertAdjacentHTML("beforeEnd", mModalHtml);
            mModalHtml = null;
            var lastModal = document.querySelector(".mModal:last-of-type"); // 使用最后一个遮罩层
            if (document.querySelectorAll(".mModal").length > 1) {
                lastModal.style.opacity = 0.01 // 已经有遮罩不覆盖
            } else {
                lastModal.style.opacity = 0.2; // 新弹遮罩0.2透明度
            }
            // 遮罩宽高
            lastModal.style.width = window.innerWidth + "px";
            //lastModal.style.height = window.innerHeight + "px";
            lastModal.style.height = document.body.scrollHeight + "px"; // 网页全文遮盖
            lastModal.style.backgroundColor = "#000";
            // 遮罩定位
            lastModal.style.position = "absolute";
            lastModal.style.left = 0;
            lastModal.style.top = 0;

            lastModal.style.zIndex = window._dlgBaseDepth++; // 保证新的遮罩在最上边
            return lastModal
        },
        _showLoading:function(tipContent, isShowModal){
            // 规避已经弹出
            if (document.querySelector("#mLoading")) {
                return
            }
            if (module.exports._notExist(tipContent)) {
                tipContent = ""
            }
            if (module.exports._notExist(isShowModal)) {
                isShowModal = false // 默认不弹出遮罩层
            }
            // 初始化对话框深度
            module.exports._initDefaultDepthOnce();

            var bodyDOM = document.querySelector("body"),
                windowInnerWidth = window.innerWidth,
                windowInnerHeight = window.innerHeight,
                mLoadingHtml = null,
                mLoadingModal = null;

            // 需要遮罩
            if (isShowModal) {
                mLoadingModal = module.exports._createModal(); // 创建Modal层
                mLoadingModal.id = "mLoadingModal" // 标记id
            }

            // 创建、追加DOM
            mLoadingHtml = '<div id="mLoading"><div class="lbk"></div><div class="lcont">' + tipContent + "</div></div>";
            bodyDOM.insertAdjacentHTML("beforeEnd", mLoadingHtml); // 把对话框插入到body标签结束标记前

            // 定位DOM
            var mLoadingDOM = document.querySelector("#mLoading");
            mLoadingDOM.style.left = 0.5 * (windowInnerWidth - mLoadingDOM.clientWidth) + "px";
            mLoadingDOM.style.top = bodyDOM.scrollTop + 0.5 * (windowInnerHeight - mLoadingDOM.clientHeight) + "px";
            return mLoadingDOM
        },
        show: function(tipContent, isShowModal){
            module.exports._showLoading(tipContent, isShowModal);
        },
        hide: function() {
            var mLoading = document.querySelector("#mLoading");
            if (mLoading) {
                mLoading.parentNode.removeChild(mLoading) // 移除Loading框
            }
            var mLoadingModal = document.querySelector("#mLoadingModal");
            if (mLoadingModal) {
                mLoadingModal.parentNode.removeChild(mLoadingModal) // 移除遮罩层
            }
        },
        refresh: function(msg) {
        	
        }

    };
});