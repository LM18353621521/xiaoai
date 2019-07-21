// pages/agent/apply/apply.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    layer_switch: 0,
    birthday: '2000-01-01',
    xy_sel: 0,
    //验证码
    mobile: "",
    code_btn_show: 1,
    second: 60,
    code_mobile: "",
    code_code: "",
  },
  /**提交申请 */
  formSubmit: function(e) {
    console.log(e);
    var that = this;
    var xy_sel = that.data.xy_sel;
    var code_mobile = that.data.code_mobile;
    var code_code = that.data.code_code;
    var data = e.detail.value;
    data.birthday = that.data.birthday;
    if (data.mobile == "" || data.mobile.length < 11) {
      app.alert("请输入11位手机号码");
      return false;
    }
    if (data.code == "") {
      app.alert("请输入您收到的验证码");
      return false;
    }
    if (data.real_nmae == "") {
      app.alert("请输入姓名");
      return false;
    }
    if (data.wechat == "") {
      app.alert("请输入微信号");
      return false;
    }
    if (data.birthday == "") {
      app.alert("请输入选择生日");
      return false;
    }
    if (data.mobile != code_mobile) {
      app.alert("手机号码输入不正确");
      return false;
    }
    if (data.code != code_code) {
      app.alert("验证码输入不正确");
      return false;
    }
    if (xy_sel != 1) {
      app.alert('请认真阅读协议，并点击同意');
      return false;
    }
    if (that.data.can_click == 0) {
      return false
    }
    data.loading = 0;
    that.setData({
      can_click: 0,
    })
    app.operation('Agent/apply_do', data, function(data) {
      console.log(data);
      if (data.status == 1) {
        app.alert(data.msg, function() {
          wx.switchTab({
            url: '/pages/user/index/index',
          })
        })
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },
  /**取消申请 */
  formReset: function(e) {
    console.log(e)
    wx.navigateBack({
      delta: 1,
    })
  },

  /** 发送验证码 */
  getcode: function(e) {
    var that = this;
    var type = e.currentTarget.dataset.type;
    if (that.data.can_click == 0) {
      return false
    }
    var mobile = that.data.mobile;
    if (mobile == "" || mobile.length < 11) {
      app.alert("请输入11位手机号码");
      return false;
    }
    that.setData({
      can_click: 0,
    })
    app.operation('Api/sendSmsCode', {
      loading: 0,
      mobile: mobile,
      type: type,
    }, function(data) {
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
          icon: 'none',
          duration: 2000
        })
        that.setData({
          code_mobile: data.data.code_mobile,
          code_code: data.data.code_code,
          second: 60,
          can_click: 1,
          code_btn_show: 0,
        })
        countdown(that);
      } else {
        app.alert(data.msg);
        that.setData({
          code_mobile: '',
          code_code: '',
          can_click: 1,
        })
      }
    });
  },

  /**勾选协议 */
  xy_select: function(e) {
    this.setData({
      xy_sel: !this.data.xy_sel,
    })
  },
  xy_open: function(e) {
    this.setData({
      layer_switch: 1,
    })
  },
  xy_close: function(e) {
    this.setData({
      layer_switch: 0,
    })
  },

  bindDateChange: function(e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      birthday: e.detail.value
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var _this = this;
    app.getData('Agent/apply', this, {
      loading: 1
    }, function(data) {
      _this.setData({
        mobile: data.data.user.mobile,
      })
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})

/**短信倒计时 */
function countdown(that) {
  var second = that.data.second;
  if (second <= 0) {
    that.setData({
      code_btn_show: 1,
      second: 60,
    });
    return;
  }
  var time = setTimeout(function() {
    that.setData({
      second: second - 1
    });
    countdown(that);
  }, 1000)
}