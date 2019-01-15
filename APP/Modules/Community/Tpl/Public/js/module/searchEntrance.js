/**
 * @file searchEntrance.js
 * 搜索模块
 *
 * @author jinhuiguo, jinhuiguo@tencent.com
 * @version
 * @date 2014-08-19
 *
 */

define('module/searchEntrance', ['lib/scroll', 'module/thread'], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    var libScroll = require('lib/scroll');
    var thread = require('module/thread');
    module.exports =  {
        //搜索类型 all/site/thread
        searchType: 'all',
        //已搜索过的列表的长度
        storeLen: 3,
        page: 1,
        isLoadingNew: true,
        isLoading: true,
        isAutoLoad: false,
        searchStoreKey: "local_search_key",
        init: function() {
            //事件
            module.exports.bindEvents();
            //初妈化历史搜索记录
            module.exports.initSearch();
            // 图片横滑初始化
            libScroll.initScroll({ulSelector:'.slideBox ul', isFlip:false, cSelector:'.warp'});
            // lazyload
            initLazyload('.warp img');
        },
        bindEvents: function() {
            var searchCon = jq('.searchCon'),
                evtIconArrow = jq('.evtIconArrow'),
                categoryList = searchCon.find('.searchList'),
                cancelIcon = searchCon.find('.cancel'),
                searchInput = searchCon.find('.evtSearch'),
                evtCategoryList = searchCon.find('.evtCategoryList'),
                searchResult = jq('.searchResult'),
                loadNext = jq('#loadNext'),
                searchBtn = jq('.searchBtn'),
                evtRecordList = searchResult.find('.evtRecordList'),
                searchArr = JSON.parse(localStorage.getItem(module.exports.searchStoreKey)) || [];
            /*
            ** [type]    all/site/thread
            ** [sId]     站点ID
            ** [word]    搜索关键字
            ** [page]    页面码
            ** [num]     每页数据个数
            ** [sSource] 站点来源类型(应用宝=2，其它不传)
            ** [sortType] 排序类型 rel = 按相关性排序 like = 按赞数排序(热门话题) time = 按时间排序(最新话题)
            */
            var searchObj = {
                sId: window.sId,
                word: '',
                page: 1,
                num: 0,
                sortType: 'rel',
                sSource: ''
            };
            //如果URL有搜索参数
            var hasQuery = jq.DIC.getQuery('query');
            if(hasQuery){
                var hasSid = jq.DIC.getQuery('sId'),
                    hasObj = {};
                hasObj.hasQuery = 'hasQuery';
                if(hasSid){hasObj.hasSid = 'hasSid'};
                searchObj.sId = jq.DIC.getQuery('sId') || 0;
                searchInput.val(decodeURIComponent(hasQuery));
                //搜索方法
                module.exports.startSearchFn(searchArr, searchObj, searchInput, hasObj);
                module.exports.isAutoLoad = true;
            }

            jq(document).on('click', '.evtIconArrow, .cancel, .evtCategoryList, .searchBtn, .clearData, .evtRecordList, .communityTit, .evtTopic, .evtUserName, .evtMoreSite', function(e){
                var thisObj = jq(this);

                //模拟手指触摸效果
                jq.DIC.touchStateNow(thisObj);

                //搜索分类图标点击
                if(thisObj.hasClass('evtIconArrow')){
                    var iconArrow = thisObj.find('span');
                    //分类隐藏状态
                    if(iconArrow.hasClass('iconArrowDown')){
                        iconArrow.removeClass('iconArrowDown').addClass('iconArrowUp');
                        categoryList.show();
                    }else{
                        iconArrow.removeClass('iconArrowUp').addClass('iconArrowDown');
                        categoryList.hide();
                    }
                }

                //删除搜索内容
                if(thisObj.hasClass('cancel')){
                    searchInput.val('');
                    searchInput.focus();
                }

                //提交搜索
                if(thisObj.hasClass('searchBtn')){
                    if(searchBtn.text() == '取消'){
                        module.exports.initSearchBtn();
                        return false;
                    }
                    module.exports.searchType = evtCategoryList.find('.current').attr('data-type');
                    module.exports.isLoadingNew = true;
                    module.exports.searchType == 'site' ? searchObj.num = 10 :  searchObj.num = 0;
                    //搜索方法
                    module.exports.startSearchFn(searchArr, searchObj, searchInput);
                }

                //清除历史搜索记录
                if(thisObj.hasClass('clearData')){
                    searchArr = [];
                    localStorage.removeItem(module.exports.searchStoreKey);
                }

                //搜索记录点击
                if(thisObj.hasClass('evtRecordList')){
                    var searchVal = thisObj.find('p').text();
                    searchInput.val(searchVal);

                    //点搜索纪录设置为搜索全部
                    evtCategoryList.find('a').removeClass('current');
                    jq(evtCategoryList.find('a')[0]).addClass('current');

                    module.exports.searchType = evtCategoryList.find('.current').attr('data-type');
                    module.exports.isLoadingNew = true;
                    module.exports.searchType == 'site' ? searchObj.num = 10 :  searchObj.num = 0;
                    //搜索方法
                    module.exports.startSearchFn(searchArr, searchObj, searchInput);
                    pgvSendClick({hottag:'search.history.serp.all'});
                }

                //话题列表社区名称点击
                if(thisObj.hasClass('communityTit')){
                    e.stopPropagation();
                    pgvSendClick({hottag:'search.all.serp.threadsite'});
                    var url = thisObj.attr('data-link');
                    //搜索方法
                    jq.DIC.reload(url);
                }

                //话题点击
                if(thisObj.hasClass('evtTopic')){
                    e.stopPropagation();
                    var url = thisObj.attr('data-link');
                    //搜索方法
                    jq.DIC.reload(url);
                }

                //话题列表用户名点击
                if(thisObj.hasClass('evtUserName')){
                    e.stopPropagation();
                    pgvSendClick({hottag:'search.all.serp.username'});
                    var url = thisObj.attr('data-link');
                    //搜索方法
                    jq.DIC.reload(url);
                }

                //查看更多社区
                if(thisObj.hasClass('evtMoreSite')){
                    module.exports.searchType = 'site';
                    searchObj.num = 10;

                    //设置为搜索社区
                    evtCategoryList.find('a').removeClass('current');
                    jq(evtCategoryList.find('a')[1]).addClass('current');

                    //搜索方法
                    module.exports.startSearchFn(searchArr, searchObj, searchInput);
                    return false;
                }

                //搜索分类点击
                if(thisObj.hasClass('evtCategoryList')){
                    var categoryName = thisObj.find('a').text(),
                        dataType = thisObj.find('a').attr('data-type');
                    e.stopPropagation();

                    evtCategoryList.find('a').removeClass('current');
                    thisObj.find('a').addClass('current');
                    searchInput.attr('placeholder', categoryName);
                    module.exports.searchType = dataType;
                    
                    if(searchInput.val() != ''){
                        module.exports.searchType = evtCategoryList.find('.current').attr('data-type');
                        module.exports.isLoadingNew = true;
                        module.exports.searchType == 'site' ? searchObj.num = 10 :  searchObj.num = 0;
                        //搜索方法
                        module.exports.startSearchFn(searchArr, searchObj, searchInput);
                    }

                    //初始化分类图标状态
                    setTimeout(function(){
                        evtIconArrow.find('span').removeClass('iconArrowUp').addClass('iconArrowDown')
                        categoryList.hide();
                    }, 200);
                    switch(dataType){
                        case 'all':
                            pgvSendClick({hottag:'search.all.serp.all'});
                            break;
                        case 'site':
                            pgvSendClick({hottag:'search.all.serp.site'});
                            break;
                        case 'thread':
                            pgvSendClick({hottag:'search.all.serp.thread'});
                            break;
                    }
                    
                }

                //初始化搜索按钮文字显示
                module.exports.initSearchBtn();
                return false;
            });
            
            //输入框事件
            searchInput.focus(function() {
                module.exports.initSearchBtn();
                module.exports.initSearch();
                searchResult.show();
                cancelIcon.show();
            }).blur(function() {
                searchTimer = setTimeout(function() {
                    searchResult.hide();
                    cancelIcon.hide();
                }, 200);
            }).keyup(function(){
                module.exports.initSearchBtn();
                searchResult.show();
                cancelIcon.show();
            })

            //输入时按下回车
            searchInput.on('keydown', function(e){
                if(e.keyCode==13){
                    searchInput.blur();
                    module.exports.searchType = evtCategoryList.find('.current').attr('data-type');
                    module.exports.isLoadingNew = true;
                    module.exports.searchType == 'site' ? searchObj.num = 10 :  searchObj.num = 0;
                    module.exports.startSearchFn(searchArr, searchObj, searchInput);
                    return false;
                }
            });

            //点击空白区域隐藏搜索分类
            jq(document).on('click', function(){
                evtIconArrow.removeClass('iconArrowUp').addClass('iconArrowDown');
                categoryList.hide();
                module.exports.initSearchBtn();
            });

            //滚屏自动加载
            var loadingPos = jq('#loadNextPos');
            var scrollPosition = jq(window).scrollTop();
            jq(window).scroll(function() {
                if (scrollPosition < jq(window).scrollTop()) {
                    if (!module.exports.isLoading && module.exports.isLoadingNew) {
                        var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
                        if (loadingObjTop <= 100) {
                            module.exports.isLoading = true;
                            jq('#loadNext').stop(true, true).slideDown('fast');
                            //如果是URL带参数访问，添加SID参数
                            if(module.exports.isAutoLoad == true){
                                searchObj.sId = jq.DIC.getQuery('sId') || 0;
                            }else{
                                searchObj.sId = 0;
                            }
                            searchObj.page = module.exports.page;
                            module.exports.search(searchObj, 'more');
                        }
                    }
                }
                scrollPosition = jq(window).scrollTop();
            });
        },
        //初始化搜索按钮
        initSearchBtn: function(){
            var searchBtn = jq('.searchBtn');
            var searchInput = jq('.evtSearch');
            var BtnText = searchBtn.text();
            if(searchInput.val() == '' && searchInput.is(':focus')){
                searchBtn.text('取消');
            }else{
                searchBtn.text('搜索');
            }
        },
        //初始化搜索
        initSearch: function() {

            var searchArr = JSON.parse(localStorage.getItem(module.exports.searchStoreKey)),
                searchResult = jq('.searchResult');
            if (searchArr != null && searchArr.length > 0) {

                //初始化“已搜索过的列表中的li标签”
                var itemStr = '';
                var encodeHtml = function(c) {
                    return {
                        '<': '&lt;',
                        '>': '&gt;',
                        '&': '&amp;',
                        '"': '&quot;'
                    }[c];
                }

                for (var i = 0, len = searchArr.length; i < len; i++) {
                    if (i < module.exports.storeLen) {
                        searchArr[i] = decodeURIComponent(searchArr[i]);
                    }
                }

            } else {
                searchArr = new Array();
            }
            var tmpl = template.render('tmpl_search_history', {'history': searchArr});
            searchResult.html(tmpl);
            
        },
        search: function(searchObj, loadTYpe, hasObj) {
            var obj = {},
                optsSite = {},
                optsThread = {},
                type = module.exports.searchType || 'all',
                sId = searchObj.sId || 0,
                word = jq.DIC.trim(searchObj.word) || '',
                page = searchObj.page || 1,
                num = searchObj.num || 0,
                sortType = searchObj.sortType || 'rel',
                sSource = searchObj.sSource || '';
            obj = {
                word: word,
                page: page,
                CSRFToken: window.CSRFToken
            }

            //在站点内搜索时传sId 
            if(sId != 0){
                obj.sId = sId; 
            }
            //站点来源类型(应用宝=2，其它不传) 
            if(sSource == 2){
                obj.sSource = sSource; 
            }
            //请求每页数据个数，默认不传
            if(num != 0){
                obj.num = num;
            }
            
            //社区搜索回调函数
            optsSite = {
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status !== 0 || !re.data) {
                        jq('#loadNext').stop(true, true).hide();
                        if(loadTYpe != 'new'){
                            jq('#showAll').show();
                        }
                        return false;
                    }
                    //如果结果为空，显示已加载全部
                    if (jq.DIC.isObjectEmpty(re.data.siteList)) {
                        jq('#loadNext').stop(true, true).hide();
                        if(loadTYpe != 'new'){
                            jq('#showAll').show();
                        }
                        if(loadTYpe == 'more'){
                            return false;
                        }
                    }
                    module.exports.page++;
                    jq('#loadNext').hide();
                    module.exports.searchCallback(re, 'site', loadTYpe);
                }
            }
            //话题搜索回调函数
            optsThread = {
                'success': function(re) {
                    var status = parseInt(re.errCode);
                    if (status !== 0 || !re.data) {
                        jq('#loadNext').stop(true, true).hide();
                        if(loadTYpe != 'new'){
                            jq('#showAll').show();
                        }
                        return false;
                    }
                    //如果结果为空，显示已加载全部
                    if (jq.DIC.isObjectEmpty(re.data.threadList)) {
                        module.exports.isLoadingNew = false;
                        jq('#loadNext').stop(true, true).hide();
                        if(loadTYpe != 'new'){
                            jq('#showAll').show();
                        }
                        if(loadTYpe == 'more'){
                            return false;
                        }
                    }
                    module.exports.page++;
                    jq('#loadNext').hide();
                    module.exports.searchCallback(re, 'thread', loadTYpe);
                }
            }
            //如果是更多加载，不显示加载状态
            if(loadTYpe != 'new'){
                optsThread.noShowLoading = true;
                optsSite.noShowLoading = true;
            }
            if(type){

                var siteUrl = 'http://api.wsq.qq.com/search/site?';
                var threadUrl = 'http://api.wsq.qq.com/search/thread?';

                if( (type == 'site' && loadTYpe == 'more') || (type == 'site' || type == 'all') && loadTYpe == 'new' && (!hasObj.hasSid || !hasObj.hasQuery) ){

                    jq.each(obj, function(key, value){
                        //搜索社区不需要指定sId
                        if(key != 'sId'){
                            if(key == 'word'){
                                siteUrl += ''+key+'='+''+value+'';
                            }else{
                                siteUrl += '&'+key+'='+''+value+'';
                            }
                        }
                    });
                    
                    //请求社区信息
                    jq.DIC.ajax(siteUrl, {CSRFToken: window.CSRFToken}, optsSite);
                }
                if(type == 'thread' || type == 'all'){

                    jq.each(obj, function(key, value){
                        //话题不需要指定num
                        if(key != 'num'){
                            if(key == 'word'){
                                threadUrl += ''+key+'='+''+value+'';
                            }else{
                                threadUrl += '&'+key+'='+''+value+'';
                            }
                        }
                        
                    });
                    //请求话题信息
                    jq.DIC.ajax(threadUrl, {CSRFToken: window.CSRFToken}, optsThread);
                }
            }
            
        },
        startSearchFn: function(searchArr, searchObj, searchInput, hasObj){
            var hasObj = hasObj || {};
            var content = jq.DIC.trim(searchInput.val());
            if (jq.DIC.mb_strlen(content) <= 0) {

                jq.DIC.dialog({
                    content: '搜索话题不能为空',
                    autoClose: true
                });
                return false;

            } else {

                var isInArrayLen = jq.inArray(encodeURIComponent(content), searchArr);
                if(hasObj.hasQuery == 'hasQuery'){
                    searchObj.sId = jq.DIC.getQuery('sId');
                }else{
                    searchObj.sId = 0;
                    module.exports.isAutoLoad = false;
                }
                //新搜索时初始化页码和loading状态
                module.exports.page = searchObj.page = 1;
                module.exports.initLoadingDefault();

                if (searchArr.length >= module.exports.storeLen - 1) {
                    searchArr = searchArr.slice(0, module.exports.storeLen - 1);
                }
                if (isInArrayLen > -1) {
                    var contentStr = encodeURIComponent(content);
                    var keyArr = [];
                    jq.each(searchArr, function(key, value){
                        if(contentStr == value){
                            //删除匹配的数据元素
                            keyArr.push(key);
                        }
                    });
                    jq.each(keyArr, function(key, value){
                        searchArr.splice(value-key, 1);
                    });
                    searchArr.unshift(encodeURIComponent(content));
                }else{
                    searchArr.unshift(encodeURIComponent(content));
                };
                localStorage.removeItem(module.exports.searchStoreKey);
                localStorage.setItem(module.exports.searchStoreKey, JSON.stringify(searchArr));
            }
            searchObj.word = content;
            //未设置社区加载个数
            if(searchObj.num == 0){
                searchObj.num = 4;
            }
            //新搜索时清空dom，否则影响去重逻辑
            jq('#evtSiteList').html('');
            jq('#evtThreadList').html('');
            //执行搜索请求
            module.exports.search(searchObj, 'new', hasObj);
        },
        searchCallback: function(re, searchType, loadTYpe){
            
            if(searchType == 'site'){
                var contentWrap = jq('#evtSiteList');
                var obj = {
                    data: re.data, 
                    totalCount: re.data.totalCount, 
                    loadMore: loadTYpe
                }
                if(module.exports.searchType == 'site'){
                    obj.onlySite = true;
                }
                var tmpl = template.render('tmpl_search_site', obj);
            }else{
                var contentWrap = jq('#evtThreadList');
                var tmpl = template.render('tmpl_search_thread', {data: re.data, totalCount: re.data.totalCount, loadMore: loadTYpe});
            }
            
            if (tmpl == '{Template Error}') {
                tmpl = '<div class="searchNull"><i class="iconEmpty cf cFont db"></i><p>出现错误，请刷新重试！</p></div>';
            }
            if(loadTYpe == 'new'){
                jq('.resultCon').show();
                contentWrap.html(tmpl);
                
            }else{
                contentWrap.append(tmpl);
            }
            module.exports.isLoading = false;
            // 图片张数初始化
            thread.initScrollImage();
        },
        initLoadingDefault: function(){
            jq('#loadNext').stop(true, true).hide();
            jq('#showAll').hide();
        }
    };
    module.exports.init();
});
