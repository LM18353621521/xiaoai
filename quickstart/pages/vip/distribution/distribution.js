// pages/vip/distribution/distribution.js
Page({

  /**
   * 页面的初始数据
   */
  data: {    
    show: 0,
    fuhide: 0,
    fuhidea: 0,
    status: 0,
  },
  swichNav: function (e) {
    var that = this;
    var status = e.target.dataset.current;
    this.setData({
      status: status,
      show: status
    });
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
  showa: function (e) {
    this.setData({
      fuhidea: 1,
    })
  },
  closea: function (e) {
    this.setData({
      fuhidea: 0,
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