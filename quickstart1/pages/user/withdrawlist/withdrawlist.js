// pages/vip/distribution/distribution.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    show: 0,
    show: 0,
    fuhide: 0,
    fuhidea: 0,
    money: '',
  },

  /**
   * 提现
   */
  withdraw_do: function (e) {
    var that = this;
    if (that.data.can_cliak == 0) {
      return false
    }
    var vip = that.data.vip;
    var data = {
      money: that.data.money,
    }

    if (!data.money) {
      app.alert('！请输入提现金额');
      return false;
    }

    if (data.money > vip.income) {
      app.alert('您的可提现金额为:' + vip.income + "元");
      return false;
    }

    that.setData({
      can_click: 0,
    })
    app.operation('Vip/withdraw', data, function (res) {
      if (res.ret == 1) {
        wx.showToast({
          title: res.msg,
        })
        vip.income -= data.money;
        that.setData({
          vip: vip,
          money: '',
          fuhide: 0,
          can_click: 1,
        });
        getList(that, 1);
      } else {
        app.alert(res.msg);
        that.setData({
          can_click: 0,
        })
      }
    });
  },
  /**
   * 输入提箱金额
   */
  set_money: function (e) {
    console.log(e.detail.value);
    this.setData({
      money: e.detail.value,
    })
  },

  swichNav: function (e) {
    var that = this;
    var status = e.target.dataset.current;
    this.setData({
      status: status,
    });
    getList(this, 1);
  },
  show: function (e) {
    this.setData({
      fuhide: 1,
    })
  },
  close: function (e) {
    this.setData({
      fuhide: 0,
    })
  },


  swichNav: function (e) {
    var that = this;
    var status = e.target.dataset.current;
    this.setData({
      status: status,
      show: status
    });

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
    var that = this;
    app.getData('Vip/withdrawlist', this, { loading: 1 }, function (data) {
      console.log(data)
    });
    getList(this, 1);
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

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
    getList(this, 0);
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})

var getList = function (that, page) {
  if (that.data.can_click == 0) return false;
  var more_tips = '正在加载中...';
  var no_order = 0;
  that.setData({
    no_order: 0,
    can_click: 0,
    more_tips: more_tips,
  })
  var data = {
    loading: 0,
    status: that.data.status,
    keyword: '',
    sort: 'create_time',
    asc: 'desc',
    pagenum: 20,
  }
  console.log(data)
  app.getList('Vip/withdraw_log', that, data, page, function (data) {
    if (page == 1 && data.data.length == 0) {
      no_order = 1;
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '上拉加载更多';
    }
    that.setData({
      no_order: no_order,
      can_click: 1,
      more_tips: more_tips,
    })
  })
}