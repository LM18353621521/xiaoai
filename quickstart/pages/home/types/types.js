// pages/selectorder/selectorder.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    sex: 0,
    dataList:[],
    categoryChild:[],
    categoryParent:[],
    pid:0,
  },
  /**
   *商品 搜索
   */
  search_do: function (e) {
    var keyword = e.detail.value;
    if (!keyword) {
      wx.showToast({
        title: '请输入关键词',
        icon: 'success',
        // image:"/images/edit.png",
        duration: 1500
      });
      return false;
    }
    wx.navigateTo({
      url: '/pages/home/goodsList/goodsList?keyword=' + keyword,
    })
  },

  /**
   * 获取下级分类
   */
  getCategoryChild:function(e){
    var pid=this.data.pid;
    app.getData('Home/categoryChild', this, { loading: 0,pid:pid}, function (data) {
    })
  },
  radiobtn: function (e) {
    var index = e.currentTarget.dataset.index;
    var pid = this.data.dataList[index].id;
    this.setData({
      sex: index,
      pid:pid,
    });
    this.getCategoryChild();       
  },
  onLoad: function (options) {
    wx.hideShareMenu({});
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    var that =this;
    app.getData('Home/category', this, { loading:1}, function (data) {
      var pid = data.data.dataList[0].id;
      that.setData({
        pid:pid,
      });  
      that.getCategoryChild();       
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