<?php
/*

 主要涉及双向回拨、短信以及互联网语音等方面的函数实现
 */

include_once ("CCPRestSDK.class.php");
/**
 * 双向回呼
 * @param from 主叫电话号码
 * @param to 被叫电话号码
 * @param customerSerNum 被叫侧显示的客服号码
 * @param fromSerNum 主叫侧显示的号码
 * @param promptTone 自定义回拨提示音
 */
function callBack($from, $to, $customerSerNum, $fromSerNum, $promptTone, $subID, $subToken, $voipAccount, $voipPwd) {
    //主账号
    $AccountSid = C('AccountSid');
    //主帐号Token
    $AccountToken = C('AccountToken');
    //应用Id
    $appId = C('appId');
    //请求地址，格式如下，不需要写https://
    $serverIP = C('serverIP');
    //请求端口
    $serverPort = C('serverPort');
    //REST版本号
    $softVersion = C('softVersion');

    // 初始化REST SDK
    $rest = new REST($serverIP, $serverPort, $softVersion);
    $rest -> setAccount($AccountSid, $AccountToken);
    //设置主账号
    $rest -> setAppId($appId);

    //将获取到的子账户信息存到REST中
    $rest -> setSubAccount($subID, $subToken, $voipAccount, $voipPwd);
    //echo '使用的子账户:'.$subAcc->statusCode.'---'.$subAcc->totalCount.'---'.$subAcc->SubAccount->subAccountSid.'---'.$subAcc->SubAccount->subToken.'---'.$subAcc->SubAccount->dateCreated.'---'.$subAcc->SubAccount->voipAccount.'---'.$subAcc->SubAccount->voipPwd;
    // 调用回拨接口
    // echo "Try to make a callback,called is $to <br/>";
    $result = $rest -> callBack($from, $to, $customerSerNum, $fromSerNum, $promptTone);
    if ($result == NULL) {
        //echo "result error!";
        return array('msgCode' => $result -> statusCode, 'msgInfo' => '请求失败01');
    }
    if ($result -> statusCode != "000000") {
        return array('msgCode' => $result -> statusCode, 'msgInfo' => '请求失败02');
        //TODO 添加错误处理逻辑
    } else {
        //echo "callback success!<br>";
        // 获取返回信息
        $callback = $result -> CallBack;
        return array('msgCode' => $result -> statusCode, 'msgInfo' => '请求成功', 'callSid' => $callback -> callSid, 'dateCreated' => $callback -> dateCreated);
        //TODO 添加成功处理逻辑
    }
}

/*
 * 创建子账户
 */
function createNewAccount($accountname) {
    //主账号
    $AccountSid = C('AccountSid');
    //主帐号Token
    $AccountToken = C('AccountToken');
    //应用Id
    $appId = C('appId');
    //请求地址，格式如下，不需要写https://
    $serverIP = C('serverIP');
    //请求端口
    $serverPort = C('serverPort');
    //REST版本号
    $softVersion = C('softVersion');

    // 初始化REST SDK
    $rest = new REST($serverIP, $serverPort, $softVersion);
    $rest -> setAccount($AccountSid, $AccountToken);
    //设置主账号
    $rest -> setAppId($appId);

    //获取子账户由系统管理员另外操作
    //创建子账户
    $subAcc = $rest -> createSubAccount($accountname);
    if ($subAcc -> statusCode != "000000") {
        //获取子账户失败
        return array('msgCode' => $subAcc -> statusCode, 'msgInfo' => '子账户创建失败');
    } else {
        return array('msgCode' => $subAcc -> statusCode, 'msgInfo' => '子账户创建成功', 'subAccountSid' => $subAcc -> SubAccount -> subAccountSid, 'subToken' => $subAcc -> SubAccount -> subToken, 'voipAccount' => $subAcc -> SubAccount -> voipAccount, 'voipPwd' => $subAcc -> SubAccount -> voipPwd);
    }
}

/**
 * 发送模板短信
 * @param to 短信接收彿手机号码集合,用英文逗号分开
 * @param datas 内容数据
 * @param $tempId 模板Id
 */
function sendShortMsg($to, $data, $tempId) {
    //主账号
    $AccountSid = C('AccountSid');
    //主帐号Token
    $AccountToken = C('AccountToken');
    //应用Id
    $appId = C('appId');
    //请求地址，格式如下，不需要写https://
    $serverIP = C('serverIP');
    //请求端口
    $serverPort = C('serverPort');
    //REST版本号
    $softVersion = C('softVersion');

    // 初始化REST SDK
    $rest = new REST($serverIP, $serverPort, $softVersion);
    $rest -> setAccount($AccountSid, $AccountToken);
    //设置主账号
    $rest -> setAppId($appId);

    //准备发送
    $result = $rest -> sendTemplateSMS($to, $data, $tempId);
    if ($result -> statusCode != "000000") {
        //短信发送失败，错误码就是$result -> statusCode
        return array('msgCode' => $result -> statusCode, 'msgInfo' => '短信发送失败!');
    } else {
        return array('msgCode' => $result -> statusCode, 'msgInfo' => '短信发送成功!');
    }
}

/*
 * 获取访客的IP
 */
function getVisitorIP() {
    global $_SERVER;
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('REMOTE_ADDR')) {
        $ip = getenv('REMOTE_ADDR');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/*
 * 通过该函数返回指定表的所有权限名
 * $permitID指定表的操作权限id(1~15中间的某个值)
 *
 */
function getAdminPermissionName($permitID) {
    $add = FALSE;
    $del = FALSE;
    $mod = FALSE;
    $sel = FALSE;
    //初始化时默认curd为全false
    switch ($permitID) {
        case 1 :
            $add = TRUE;
            break;
        case 2 :
            $del = TRUE;
            break;
        case 3 :
            $add = TRUE;
            $del = TRUE;
            break;
        case 4 :
            $mod = TRUE;
            break;
        case 5 :
            $add = TRUE;
            $mod = TRUE;
            break;
        case 6 :
            $del = TRUE;
            $mod = TRUE;
            break;
        case 7 :
            $add = TRUE;
            $del = TRUE;
            $mod = TRUE;
            break;
        case 8 :
            $sel = TRUE;
            break;
        case 9 :
            $add = TRUE;
            $sel = TRUE;
            break;
        case 10 :
            $del = TRUE;
            $sel = TRUE;
            break;
        case 11 :
            $add = TRUE;
            $del = TRUE;
            $sel = TRUE;
            break;
        case 12 :
            $mod = TRUE;
            $sel = TRUE;
            break;
        case 13 :
            $add = TRUE;
            $mod = TRUE;
            $sel = TRUE;
            break;
        case 14 :
            $del = TRUE;
            $mod = TRUE;
            $sel = TRUE;
            break;
        case 15 :
            $add = TRUE;
            $del = TRUE;
            $mod = TRUE;
            $sel = TRUE;
            break;
        default :
            break;
    }
    $str = "";
    if ($add)
        $str .= "插入";
    if ($del) {
        if ($str != "")
            $str .= "、";
        $str .= "删除";
    }
    if ($mod) {
        if ($str != "")
            $str .= "、";
        $str .= "编辑";
    }
    if ($sel) {
        if ($str != "")
            $str .= "、";
        $str .= "查询、";
    }
    if ($str == "")
        $str = "无权限";
    return $str;
}

/*
 * 通过该函数返回指定表的某种操作权限是否被授权
 * $permitID指定表的操作权限id(1~15中间的某个值)
 * $operatorID对指定表的某种操作 分为4种：1add、2del、3mod、4sel
 */
function getAdminPermission($permitID, $operatorID) {
    $add = FALSE;
    $del = FALSE;
    $mod = FALSE;
    $sel = FALSE;
    //初始化时默认curd为全false
    switch ($permitID) {
        case 1 :
            $add = TRUE;
            break;
        case 2 :
            $del = TRUE;
            break;
        case 3 :
            $add = TRUE;
            $del = TRUE;
            break;
        case 4 :
            $mod = TRUE;
            break;
        case 5 :
            $add = TRUE;
            $mod = TRUE;
            break;
        case 6 :
            $del = TRUE;
            $mod = TRUE;
            break;
        case 7 :
            $add = TRUE;
            $del = TRUE;
            $mod = TRUE;
            break;
        case 8 :
            $sel = TRUE;
            break;
        case 9 :
            $add = TRUE;
            $sel = TRUE;
            break;
        case 10 :
            $del = TRUE;
            $sel = TRUE;
            break;
        case 11 :
            $add = TRUE;
            $del = TRUE;
            $sel = TRUE;
            break;
        case 12 :
            $mod = TRUE;
            $sel = TRUE;
            break;
        case 13 :
            $add = TRUE;
            $mod = TRUE;
            $sel = TRUE;
            break;
        case 14 :
            $del = TRUE;
            $mod = TRUE;
            $sel = TRUE;
            break;
        case 15 :
            $add = TRUE;
            $del = TRUE;
            $mod = TRUE;
            $sel = TRUE;
            break;
        default :
            break;
    }

    switch ($operatorID) {
        case 1 :
            //增
            return $add;
        case 2 :
            //删
            return $del;
        case 3 :
            //改
            return $mod;
        case 4 :
            //查
            return $sel;
        default :
            return FALSE;
    }

}
?>