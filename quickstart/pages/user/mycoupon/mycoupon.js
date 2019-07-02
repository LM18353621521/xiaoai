// pages/user/mycoupon/mycoupon.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    _active: 0,
    headerArray: ['全部', '未使用', '已使用', '已过期'],
    couponArray: "",
    userstatus: "",
    title: "正在加载中...",
    couponArray:[
      {},
      {}
    ]
  },
  headertap: function (e) {
    var that = this;
    var header = {
      'content-type': 'application/x-www-form-urlencoded',
    };
    var $type = e.target.dataset.idx
    that.setData({
      loadinghidden: true,
      _active: $type
    });
    getList(this, 1);
  },

  headertaps: function (e) {
    this.setData({
      _active: e.target.dataset.idx
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
    wx.hideShareMenu({})
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
    sort: 'create_time',
    asc: 'desc',
    pagenum: 10,
  }
  app.getList('vip/mycoupon', that, data, page, function (data) {
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