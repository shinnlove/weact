// JavaScript Document
define(function(t, e, n) {
	function i(t, e, n, i, a, o) {
		this.conNode = t, this.background = null, this.backCtx = null, this.mask = null, this.maskCtx = null, this.lottery = null, this.lotteryType = "image", this.cover = e || "#000", this.coverType = n, this.pixlesData = null, this.width = i, this.height = a, this.lastPosition = null, this.drawPercentCallback = o, this.vail = !1
	}
	var a = t("zepto");
	i.prototype = {
		createElement: function(t, e) {
			var n = document.createElement(t);
			for (var i in e) n.setAttribute(i, e[i]);
			return n
		},
		getTransparentPercent: function(t, e, n) {
			for (var i = t.getImageData(0, 0, e, n), a = i.data, o = [], r = 0, s = a.length; s > r; r += 4) {
				var l = a[r + 3];
				128 > l && o.push(r)
			}
			return (100 * (o.length / (a.length / 4))).toFixed(2)
		},
		resizeCanvas: function(t, e, n) {
			t.width = e, t.height = n, t.getContext("2d").clearRect(0, 0, e, n)
		},
		resizeCanvas_w: function(t, e, n) {
			t.width = e, t.height = n, t.getContext("2d").clearRect(0, 0, e, n), this.vail ? this.drawLottery() : this.drawMask()
		},
		drawPoint: function(t, e) {
			this.maskCtx.beginPath(), this.maskCtx.arc(t, e, 30, 0, 2 * Math.PI), this.maskCtx.fill(), this.maskCtx.beginPath(), this.maskCtx.lineWidth = 60, this.maskCtx.lineCap = this.maskCtx.lineJoin = "round", this.lastPosition && this.maskCtx.moveTo(this.lastPosition[0], this.lastPosition[1]), this.maskCtx.lineTo(t, e), this.maskCtx.stroke(), this.lastPosition = [t, e], this.mask.style.zIndex = 20 == this.mask.style.zIndex ? 21 : 20
		},
		bindEvent: function() {
			var t = this,
				e = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()),
				n = e ? "touchstart" : "mousedown",
				i = e ? "touchmove" : "mousemove";
			if (e) t.conNode.addEventListener("touchmove", function(t) {
				a && t.preventDefault(), t.cancelable ? t.preventDefault() : window.event.returnValue = !1
			}, !1), t.conNode.addEventListener("touchend", function() {
				a = !1;
				var e = t.getTransparentPercent(t.maskCtx, t.width, t.height);
				e >= 50 && "function" == typeof t.drawPercentCallback && t.drawPercentCallback()
			}, !1);
			else {
				var a = !1;
				t.conNode.addEventListener("mouseup", function(e) {
					e.preventDefault(), a = !1;
					var n = t.getTransparentPercent(t.maskCtx, t.width, t.height);
					n >= 50 && "function" == typeof t.drawPercentCallback && t.drawPercentCallback()
				}, !1)
			}
			this.mask.addEventListener(n, function(n) {
				n.preventDefault(), a = !0;
				var i = e ? n.touches[0].pageX : n.pageX || n.x,
					o = e ? n.touches[0].pageY : n.pageY || n.y;
				t.drawPoint(i, o, a)
			}, !1), this.mask.addEventListener(i, function(n) {
				if (n.preventDefault(), !a) return !1;
				n.preventDefault();
				var i = e ? n.touches[0].pageX : n.pageX || n.x,
					o = e ? n.touches[0].pageY : n.pageY || n.y;
				t.drawPoint(i, o, a)
			}, !1)
		},
		drawLottery: function() {
			if ("image" == this.lotteryType) {
				var t = new Image,
					e = this;
				t.onload = function() {
					this.width = e.width, this.height = e.height, e.resizeCanvas(e.background, e.width, e.height), e.backCtx.drawImage(this, 0, 0, e.width, e.height), e.drawMask()
				}, t.src = this.lottery
			} else if ("text" == this.lotteryType) {
				this.width = this.width, this.height = this.height, this.resizeCanvas(this.background, this.width, this.height), this.backCtx.save(), this.backCtx.fillStyle = "#FFF", this.backCtx.fillRect(0, 0, this.width, this.height), this.backCtx.restore(), this.backCtx.save();
				var n = 30;
				this.backCtx.font = "Bold " + n + "px Arial", this.backCtx.textAlign = "center", this.backCtx.fillStyle = "#F60", this.backCtx.fillText(this.lottery, this.width / 2, this.height / 2 + n / 2), this.backCtx.restore(), this.drawMask()
			}
		},
		drawMask: function() {
			if ("color" == this.coverType) this.maskCtx.fillStyle = this.cover, this.maskCtx.fillRect(0, 0, this.width, this.height), this.maskCtx.globalCompositeOperation = "destination-out";
			else if ("image" == this.coverType) {
				var t = new Image,
					e = this;
				t.onload = function() {
					e.resizeCanvas(e.mask, e.width, e.height), /android/i.test(navigator.userAgent.toLowerCase()), e.maskCtx.globalAlpha = .98, e.maskCtx.drawImage(this, 0, 0, this.width, this.height, 0, 0, e.width, e.height);
					var t = 50,
						n = a("#ca-tips").val(),
						i = e.maskCtx.createLinearGradient(0, 0, e.width, 0);
					i.addColorStop("0", "#fff"), i.addColorStop("1.0", "#000"), e.maskCtx.font = "Bold " + t + "px Arial", e.maskCtx.textAlign = "left", e.maskCtx.fillStyle = i, e.maskCtx.fillText(n, e.width / 2 - e.maskCtx.measureText(n).width / 2, 100), e.maskCtx.globalAlpha = 1, e.maskCtx.globalCompositeOperation = "destination-out"
				}, t.src = this.cover
			}
		},
		init: function(t, e) {
			t && (this.lottery = t, this.lottery.width = this.width, this.lottery.height = this.height, this.lotteryType = e || "image", this.vail = !0), this.vail && (this.background = this.background || this.createElement("canvas", {
				style: "position:fixed;left:50%;top:0;width:640px;margin-left:-320px;height:100%;background-color:transparent;"
			})), this.mask = this.mask || this.createElement("canvas", {
				style: "position:fixed;left:50%;top:0;width:640px;margin-left:-320px;height:100%;background-color:transparent;"
			}), this.mask.style.zIndex = 20, this.conNode.innerHTML.replace(/[\w\W]| /g, "") || (this.vail && this.conNode.appendChild(this.background), this.conNode.appendChild(this.mask), this.bindEvent()), this.vail && (this.backCtx = this.backCtx || this.background.getContext("2d")), this.maskCtx = this.maskCtx || this.mask.getContext("2d"), this.vail ? this.drawLottery() : this.drawMask();
			var n = this;
			window.addEventListener("resize", function() {
				n.width = document.documentElement.clientWidth, n.height = document.documentElement.clientHeight, n.resizeCanvas_w(n.mask, n.width, n.height)
			}, !1)
		}
	}, n.exports = i
})