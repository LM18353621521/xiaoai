// pages/home/discuss/discuss.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    niming: 0,
    radio: "../../../images/no.png",
    star1: 5,
    starlist1: [
      '../../../images/star-active.png',
      '../../../images/star-active.png',
      '../../../images/star-active.png',
      '../../../images/star-active.png',
      '../../../images/star-active.png',
    ],
    star2: 5,
    starlist2: [
      '../../../images/star-active.png',
      '../../../images/star-active.png',
      '../../../images/star-active.png',
      '../../../images/star-active.png',
      '../../../images/star-active.png',
    ],
    id: 0,
  },
  radiobtn: function(e) {
    var niming = this.data.niming;
    if (niming == 0) {
      niming = 1;
    } else {
      niming = 0;
    }
    this.setData({
      niming: niming
    })
  },
  /**
   * 提交
   */
  submit_do: function(e) {
    var that = this;
    var source = this.data.source;
    var ol_index = app.globalData.ol_index;
    var orderlog = this.data.orderlog;
    console.log(orderlog);

    var orderlog_json = JSON.stringify(orderlog);

    app.formSubmit('Order/comment_do', that, {
      order_id: that.data.id,
      niming: that.data.niming,
      star1: that.data.star1,
      star2: that.data.star2,
      orderlog: orderlog_json,
    }, function(data) {
      var pages = getCurrentPages();
      if (source==1) {
        var prevPage = pages[pages.length - 2]; //上一个页面
      } else {
        var prevPage1 = pages[pages.length - 2]; //上一个页面
        var prevPage = pages[pages.length - 3]; //上一个页面

        var prevPage1 = pages[pages.length - 2]; //详情页
        var prev_order = prevPage1.data.order;
        prev_order.is_comment=1;
        prevPage1.setData({
          order: prev_order,
        });
      }
      var prev_list = prevPage.data.list;
      prev_list[ol_index].is_comment = 1;
      prevPage.setData({
        list: prev_list,
      });
      app.alert(data.msg, function(data) {
        wx.navigateBack({
          delta: 1,
        })
      });
    }, function(data) {
      app.alert(data.msg);
    });

  },

  /**
   * 设置评语
   */
  set_content: function(e) {
    console.log(e);
    var orderlog = this.data.orderlog;
    var index = e.currentTarget.dataset.index;
    orderlog[index]['content'] = e.detail.value;
    console.log(orderlog);
    this.setData({
      orderlog: orderlog,
    });
  },

  starbtn: function(e) {
    var index = e.currentTarget.dataset.index;
    var indexstar = e.currentTarget.dataset.indexstar;
    var orderlog = this.data.orderlog;
    var star = [];
    for (var i = 0; i <= indexstar; i++) {
      star[i] = '../../../images/star-active.png';
    }
    for (var j = indexstar + 1; j < 5; j++) {
      star[j] = '../../../images/star.png';
    }
    orderlog[index]['star'] = indexstar + 1;
    orderlog[index]['starlist'] = star;
    this.setData({
      orderlog: orderlog,
    });
  },
  starbtna: function(e) {
    var index = e.currentTarget.dataset.index;
    var star = [];
    for (var i = 0; i <= index; i++) {
      star[i] = '../../../images/star-active.png';
    }
    for (var j = index + 1; j < 5; j++) {
      star[j] = '../../../images/star-gray.png';
    }
    this.setData({
      star1: index + 1,
      starlist1: star,
    })
  },
  starbtnb: function(e) {
    var index = e.currentTarget.dataset.index;
    var star = [];
    for (var i = 0; i <= index; i++) {
      star[i] = '../../../images/star-active.png';
    }
    for (var j = index + 1; j < 5; j++) {
      star[j] = '../../../images/star-gray.png';
    }
    this.setData({
      star2: index + 1,
      starlist2: star,
    })
  },

  //上传图片
  chooseImage: function(e) {
    var index = e.currentTarget.dataset.index;
    var orderlog = this.data.orderlog;
    var imgs = orderlog[index].imgs;
    var imgs_save = orderlog[index].imgs_save;
    var that = this
    if (imgs.length >= 9) {
      wx.showToast({
        title: '最多上传9张图片',
      });
      return false;
    }

    /**
     * 上传图片
     */
    wx.chooseImage({
      count: 9,
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function(res) {
        console.log(res);
        var tempFilePaths = res.tempFilePaths;
        for (var i = 0; i < tempFilePaths.length; i++) {
          if (imgs.length < 9) {
            imgs.push(tempFilePaths[i]);
          }
        }
        orderlog[index].imgs = imgs;
        that.setData({
          orderlog: orderlog,
        });
        app.fileupload(tempFilePaths, [], function(files) {
          for (var i = 0; i < files.length; i++) {
            if (imgs_save.length < 9) {
              imgs_save.push(files[i]);
            }
          }
          orderlog[index].imgs_save = imgs_save;
          that.setData({
            orderlog: orderlog,
          });
        });

      }
    })

  },
  previewImage: function(e) {
    var current = e.target.dataset.src

    wx.previewImage({
      current: current,
      urls: this.data.imageList
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    console.log(options);
    wx.hideShareMenu({});
    this.setData(options)
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that = this;
    var data = {
      loading: 1,
      order_id: this.data.id,
    }
    app.getData('Order/comment', this, data, function(data) {
      var comment = [];
      var orderlog = data.data.orderlog;
      console.log(orderlog);
      var starlist = [
        '../../../images/star-active.png',
        '../../../images/star-active.png',
        '../../../images/star-active.png',
        '../../../images/star-active.png',
        '../../../images/star-active.png'
      ];
      for (var i = 0; i < orderlog.length; i++) {
        orderlog[i]['star'] = 5;
        orderlog[i]['starlist'] = starlist;
        orderlog[i]['imgs'] = [];
        orderlog[i]['imgs_save'] = [];
        orderlog[i]['content'] = "";
      }
      that.setData({
        orderlog: orderlog
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