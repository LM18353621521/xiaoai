var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    /**绑定手机 */
    f4:0,
    code_btn_show: 1,
    second: 60,
    mobile: "",
    code:"",
    code_mobile: "",
    code_code: "",
  },
  /**立即申请 */
  apply_do:function(e){
    console.log(e)
    var _this=this;
    var user = _this.data.user;
    if(user.mobile==null||user.mobile==""){
      wx.showModal({
        title: '提示',
        content: '您未绑定手机号，立即绑定？',
        showCancel: true, //是否显示取消按钮
        cancelText: "否", //默认是“取消”
        cancelColor: 'grey', //取消文字的颜色
        confirmText: "是", //默认是“确定”
        success: function (res) {
          console.log(res);
          if (res.confirm) {
            _this.setData({
              f4:1,
            })
          }
        }
      });
      return false;
    }else{
      wx.navigateTo({
        url: '/pages/agent/apply/apply',
      })
    }
  },

  /** 输入手机号 */
  input_mobile: function (e) {
    var mobile = e.detail.value;
    this.setData({
      mobile: mobile,
    });
  },
  /**输入验证码 */
  input_code: function (e) {
    var code = e.detail.value;
    this.setData({
      code: code,
    });
  },
  /**隐藏绑定手机浮层 */
  hidef4:function(e){
    this.setData({
      f4:0,
    })
  },
  /** 绑定手机  */
  bind_mobile_do: function (e) {
    var that = this;
    if (that.data.can_click == 0) {
      return false
    }
    var mobile = that.data.mobile;
    var code = that.data.code;
    var code_mobile = that.data.code_mobile;
    var code_code = that.data.code_code;
    if (mobile == "" || mobile.length < 11) {
      app.alert("请输入11位手机号码");
      return false;
    }
    if (code == "") {
      app.alert("请输入您收到的验证码");
      return false;
    }
    if (mobile != code_mobile) {
      app.alert("手机号码输入不正确");
      return false;
    }
    if (code != code_code) {
      app.alert("验证码输入不正确");
      return false;
    }
    app.operation('Vip/bind_mobile', {
      loading: 0,
      mobile: mobile
    }, function (data) {
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
          icon: 'success',
          duration: 2000
        })
        that.setData({
          mobile: '',
          code: '',
          code_mobile: "",
          code_code: "",
          f4: 0,
          can_click: 1,
          code_btn_show:1,
        })
        getInfo();
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });

  },
  /** 发送验证码 */
  getcode: function (e) {
    var that = this;
    var type = e.currentTarget.dataset.type;
    if (that.data.can_click == 0) {
      return false
    }
    if (type == 1) {
      var mobile = that.data.mobile;
    } else if (type == 5) {
      var mobile = that.data.vip.mobile;
    } else if (type == 6) {
      var mobile = that.data.mobile;
    }
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
    }, function (data) {
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
          code_btn_show:0,
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
  navigator_do: function (e) {    
    wx.navigateTo({
      url: e.currentTarget.dataset.url,
    })
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    getInfo(this);
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})
/**获取代理信息 */
function getInfo(_this){
    app.getData('Agent/index', _this, {
        loading: 1
    }, function (data) {
        console.log(data)
    })
}
/**短信倒计时 */
function countdown(that) {
  var second = that.data.second;
  if (second <= 0) {
    that.setData({
      code_btn_show:1,
      second: 60,
    });
    return;
  }
  var time = setTimeout(function () {
    that.setData({
      second: second - 1
    });
    countdown(that);
  }, 1000)
}