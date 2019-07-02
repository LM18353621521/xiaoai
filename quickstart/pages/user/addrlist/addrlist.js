// pages/user/addrlist/addrlist.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    can_click:1,
    action:1,
    addressList:[],  
  },
  /**
   * 添加编辑
   */
  address_edit:function(e){
    var address_id = e.currentTarget.dataset.address_id;
    var user = wx.getStorageSync('user');
    wx.navigateTo({
      url: '/pages/user/addredit/addredit?address_id='+address_id,
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) { 
    wx.hideShareMenu({
    }) 

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    var that =this;
    var user = wx.getStorageSync('user');
    var data ={
      vip_id:user.vip_id,
    }
    app.getData('Address/addressList', that, data, function(data){
    })
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