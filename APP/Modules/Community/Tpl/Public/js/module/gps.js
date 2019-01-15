define('module/gps', [], function(require, exports, module) {
    "require:nomunge,exports:nomunge,module:nomunge"; // yui 压缩配置，不混淆这三个变量
    module.exports =  {
        error : 0,// -1 未已开启传感器 -2 未授权 -3 无法获取当前位置 -4 超时 -5 未知错误

        mqqSetError: function(status) {
            console.log(status);
            if (status) {
                if(!status.enabled) {
                    module.exports.error = -1;
                } else if(!status.authroized) {
                    module.exports.error = -2;
                }
            }
        },

        h5SetError: function(error) {
            console.log(error);
            if (error.code) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        module.exports.error = -2;
                        break;
                    case error.POSITION_UNAVAILABLE:
                        module.exports.error = -3;
                        break;
                    case error.TIMEOUT:
                        module.exports.error = -4;
                        break;
                    case error.UNKNOWN_ERROR:
                        module.exports.error = -5;
                        break;
                }
            }
        },

        getError: function() {
            return module.exports.error;
        },

        getLocation: function(callback) {

            if (typeof callback != 'function') {
                callback = function(latitude, longitude) {
                    console.log([latitude, longitude]);
                }
            }

            var retCode = -1;
            mqq = window.mqq || '';
            if (mqq) {
                mqq.sensor.getLocation(function(retCode, latitude, longitude, status) {
                    if (retCode == 0) {
                        callback(latitude, longitude);
                    }
                });
            }

            if (!mqq || retCode != 0) {
                var gps = navigator.geolocation;

                if (!gps) {
                    console.log('not support gps');
                    module.exports.mqqSetError(status);
                    callback(undefined, undefined);
                    return false;
                }

                // gps 感应器
                gps.getCurrentPosition(
                    function(position) {
                        console.log(position);
                        if (position) {
                            callback(position.coords.latitude, position.coords.longitude);
                        } else {
                            console.log('position is null');
                            callback(undefined, undefined);
                        }
                    },
                    function(error) {
                        console.log('get gps info failed', error);
                        module.exports.h5SetError(error);
                        callback(undefined, undefined);
                    },
                    {maximumAge: 10000}
                );
            }
        }
    }
});
