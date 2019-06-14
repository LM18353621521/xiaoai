// pages/home/ordersdetail/ordersdetail.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    orderCode: '51245421245454',
    goodslist: [
      {
        img: '../../../images/product-list.jpg',
        name: 'VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环',
        color: '39吗给色',
        size: '大码',
        price: 25,
        num: 5,
      },
      {
        img: '../../../images/product-list.jpg',
        name: 'VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环VR飞机杯真实体验真人3D环',
        color: '39吗给色',
        size: '大码',
        price: 25,
        num: 5,
      },
    ],
  },
  copyCode(e) {
    var self = this;
    wx.setClipboardData({
      data: self.data.orderCode,
      success: function (res) {
        wx.showModal({
          title: '提示',
          content: '复制成功',
          success: function (res) {
            if (res.confirm) {
              console.log('确定')
            } else if (res.cancel) {
              console.log('取消')
            }
          }
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