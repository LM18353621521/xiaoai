var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    agent_status: 0,
    desc: "12325434565468",
    mIndex: 0,
    money: 0.00,
  },
  /**确定充值 */
  surePay: function(e) {
    var _this = this;
    var money = _this.data.money;
    if (money <= 1) {
      app.alert('请选择充值金额');
      return false;
    }
    var data ={
      money:money,
    }
    app.operation('Agent/orderadd', data, function(data) {
      var order = data.order;
      var payDtae = data.data;
      if (data.ret == 1) {
        _this.order_pay(payDtae);
      } else {
        app.alert(data.msg);
        return false;
      }
    });
  },
  /**进行支付 */
  order_pay: function(data) {
    var _this = this;
    var payargs = data;
    wx.requestPayment({
      timeStamp: payargs.timeStamp,
      nonceStr: payargs.nonceStr,
      package: payargs.package,
      signType: payargs.signType,
      paySign: payargs.paySign,
      success: function(res) {
        wx.showToast({
          title: '支付成功！',
          duration: 2000,
          success: function(e) {
            setTimeout(function(e) {
              wx.navigateBack({
                delta:1,
              })
            }, 2000)
          }
        });
      },
      fail: function(res) {
        wx.showToast({
          title: "支付失败!",
          icon: "none",
          duration: 2500,
          success: function(e) {
            setTimeout(function(e) {
            }, 2500)
          }
        });
      }
    })
  },

  /**写入充值金额 */
  setMoney: function(e) {
    this.setData({
      money: e.detail.value,
    })
  },
  /**选择金额 */
  selectMoney: function(e) {
    console.log(e)
    this.setData({
      mIndex: e.currentTarget.dataset.mindex,
      money: e.currentTarget.dataset.money,
    })
  },

  /**页面跳转 */
  navigator_do: function(e) {
    wx.navigateTo({
      url: e.currentTarget.dataset.url,
    })
  },
  /**页面返回 */
  navigateBack: function(e) {
    wx.navigateBack({
      delta: e.currentTarget.dataset.delta,
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
    app.getData('Agent/recharge', this, {
      loading: 1
    }, function(data) {
      console.log(data)
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