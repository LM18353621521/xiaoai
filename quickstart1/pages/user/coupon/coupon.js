// pages/user/mycoupon/mycoupon.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    _active: 0,
    headerArray: [],
    couponArray: "",
    userstatus: "",
    title: "正在加载中...",
    couponArray: [
      {},
      {}
    ]
  },
  getcoupon: function (e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var couponList = this.data.list;
    if (couponList[index].has == 1) {
      return false;
    }
    if (this.data.can_click == 0) {
      return false;
    }
    that.setData({
      can_click: 0,
    });

    var data = {
      id: couponList[index].id,
    }
    app.operation("home/getcoupon", data, function (data) {
      console.log(data);
      if (data.ret == 1) {
        couponList[index].has = 1;
        wx.showToast({
          title: '领券成功！',
        });
        that.setData({
          can_click: 1,
          list: couponList,
        })
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
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
    wx.hideShareMenu({});
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
    status: that.data._active,
    keyword: '',
    sort: 'money',
    asc: 'desc',
    pagenum: 10,
  }
  console.log(data)
  app.getList('home/couponList', that, data, page, function (data) {
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