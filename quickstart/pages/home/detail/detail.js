// pages/exchangeinfo/exchangeinfo.js
var app = getApp();
Page({
  data: {
    can_click: 1,
    showfuceng: 1,
    f1: 0,
    f2: 0,
    f3: 0,
    start: 0,
    current: 0,
    hidefu: 0, //浮层显示隐藏
    tabcurrent: 0, //单选
    tabcurrenta: 0, //单选
    couponList: [],
    cart_num: 0,
    goods_id: 18,
    stock: 0,
    num: 1, //加减数量
    goodsInfo: [],
    filter_spec: [],
    goods_spec_price: [],
    spec_img: '',
    has_collect: 0,
    collect_num: 0,
    item_id: 0,
    cur_comment: 0,
  },

  /**
   *保存图片
   */
  save_poster: function(e) {
    console.log(e);
    var that = this;
    if (that.data.can_click == 0) {
      return false;
    }
    that.save_img_loc(that.data.poster_img);
  },
  /**
   * 我要推荐
   */
  share_do: function(e) {
    var that = this;
    if (that.data.can_click == 0) {
      return false;
    }
    that.setData({
      can_click: 0,
    });
    var index = e.currentTarget.dataset.index;
    var list = this.data.list;
    this.setData({
      goodsInfo: list[index],
    })
    this.showf3();
  },
  /**
   * 分享到朋友圈
   */
  shre_friend_circle: function(e) {
    var that = this;
    wx.getSetting({
      success(res) {
        if (!res.authSetting['scope.writePhotosAlbum']) {
          wx.authorize({
            scope: 'scope.writePhotosAlbum',
            success() {
              console.log('授权成功');
              that.create_poster();
            }
          })
        } else {
          that.create_poster();
        }
      }
    })
  },
  /**
   * 生成分享图片
   */
  create_poster: function() {
    wx.showLoading({
      title: '正在生成海报...',
    })
    var that = this;
    var goodsInfo = this.data.goodsInfo;
    var data = {
      goods_id: goodsInfo.id,
    }
    app.getData('Home/create_poster', that, data, function(data) {
      that.showf2();
      wx.hideLoading();
      that.setData({
        can_click: 1,
      });
    })
  },

  /**
   * 保存图片到相册
   */
  save_img_loc: function(img_path) {
    var that = this;
    var imgSrc = img_path;
    wx.downloadFile({
      url: imgSrc,
      success: function(res) {
        console.log(res);
        //图片保存到本地
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function(data) {
            wx.showToast({
              title: '保存成功',
              icon: 'success',
              duration: 2000
            })
            that.showf2();
          },
          fail: function(err) {
            console.log(err);
            if (err.errMsg === "saveImageToPhotosAlbum:fail auth deny") {
              console.log("当初用户拒绝，再次发起授权")
              wx.openSetting({
                success(settingdata) {
                  console.log(settingdata)
                  if (settingdata.authSetting['scope.writePhotosAlbum']) {
                    console.log('获取权限成功，给出再次点击图片保存到相册的提示。')
                  } else {
                    console.log('获取权限失败，给出不给权限就无法正常使用的提示')
                  }
                }
              })
            }
          },
          complete(res) {
            console.log(res);
          }
        })
      }
    })
  },

  /**
   * 立即购买
   */
  buy_now: function(e) {
    var that = this;
    if (that.can_click == 0) {
      return false
    };
    that.setData({
      can_click: 0,
    });
    var user = wx.getStorageSync('user');
    var data = {
      vip_id: user.vip_id,
      goods_id: that.data.goods_id,
      item_id: that.data.item_id,
      buy_num: that.data.num,
    }
    app.operation('api/getFormId', {formId: e.detail.formId},function(){      
    });
    wx.navigateTo({
      url: '/pages/home/orderconfirm/orderconfirm?action=buy_now&goods_id=' + data.goods_id + '&item_id=' + data.item_id + '&buy_num=' + data.buy_num,
    })
  },
  /**
   * 去购物车
   */
  cart_do: function(e) {
    console.log(e)
    wx.switchTab({
      url: '/pages/home/cart/cart',
    })
  },
  /**
   * 加入购物车
   */
  cart_add: function(e) {
    var that = this;
    if (that.data.can_click == 0) {
      return false
    };
    that.setData({
      can_click: 0,
    });
    var user = wx.getStorageSync('user');
    var data = {
      vip_id: user.vip_id,
      goods_id: that.data.goods_id,
      item_id: that.data.item_id,
      number: that.data.num,
      type: 1,
    }
    app.operation("Cart/cart_update", data, function(data) {
      console.log(data);
      console.log(1)
      if (data.ret == 1) {
        wx.showToast({
          title: data.msg,
        })
        var cart_num = that.data.cart_num;
        cart_num += that.data.num;
        that.setData({
          cart_num: cart_num,
          hidefu: 0
        })
      } else {
        app.alert(data.msg);
      }
      that.setData({
        can_click: 1,
      })
    })
  },

  getcoupon: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var couponList = this.data.couponList;
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
    app.operation("home/getcoupon", data, function(data) {
      console.log(data);
      if (data.ret == 1) {
        couponList[index].has = 1;
        wx.showToast({
          title: '领券成功！',
        });
        that.setData({
          can_click: 1,
          couponList: couponList,
        })
      } else {
        app.alert(data.msg);
        that.setData({
          can_click: 1,
        })
      }
    });
  },
  showf1: function(e) {
    var f1 = this.data.f1;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f1: 1,
      showfuceng: 0
    })
  },
  hidef1: function(e) {
    var f1 = this.data.f1;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f1: 0,
      showfuceng: 1
    })
  },
  showf2: function(e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 1,
      f3: 0,
      showfuceng: 0
    })
  },
  hidef3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 0,
      showfuceng: 1
    })
  },
  showf3: function(e) {
    var f3 = this.data.f3;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f3: 1,
      showfuceng: 0
    })
  },
  hidef2: function(e) {
    var f2 = this.data.f2;
    var showfuceng = this.data.showfuceng;
    this.setData({
      f2: 0,
      showfuceng: 1
    })
  },
  startplace: function(e) {
    this.setData({
      start: e.detail.value
    })
  },
  press: function(e) {
    var cur = e.currentTarget.dataset.current;
    this.setData({
      current: cur,
    })
  },
  spec_sel: function(e) {
    var index = e.currentTarget.dataset.index;
    var subindex = e.currentTarget.dataset.subindex;
    var filter_spec = this.data.filter_spec;
    console.log(filter_spec);
    var cur_spec = "";
    var k = 0;

    for (var i = 0; i < filter_spec[index].length; i++) {
      if (subindex == i) {
        filter_spec[index][i].sel = 1;
      } else {
        filter_spec[index][i].sel = 0;
      }
    }

    for (let key in filter_spec) {
      var items = filter_spec[key];
      console.log(items);
      if (k == 0) {
        for (var s = 0; s < items.length; s++) {
          if (items[s].sel == 1) {
            cur_spec += items[s].item;
          }
        }
      } else {
        cur_spec += "，";
        for (var s = 0; s < items.length; s++) {
          if (items[s].sel == 1) {
            cur_spec += items[s].item;
          }
        }
      }
      k++;
    }
    this.setData({
      cur_spec: cur_spec,
      tabcurrent: index,
      filter_spec: filter_spec,
    });
    initGoodsPrice(this);
  },
  singesizebtn: function(e) {
    var index = e.currentTarget.dataset.index;
    var sizeArr = this.data.sizeArr;
    var size = sizeArr[index];
    this.setData({
      tabcurrenta: index,
      size: size
    })
  },
  /**
   * 收藏操作
   */
  collect_do: function(e) {
    var that = this;
    if (that.data.can_click == 0) {
      return false;
    }
    that.setData({
      can_click: 0,
    });
    var has_collect = this.data.has_collect;
    var collect_num = this.data.collect_num;
    var user = wx.getStorageSync('user');
    var coolect_type = has_collect == 0 ? 1 : 2;
    var data = {
      vip_id: user.vip_id,
      goods_id: that.data.goods_id,
      type: coolect_type,
    }
    app.operation("home/collect_do", data, function(data) {
      if (has_collect == 0) {
        collect_num++;
        that.setData({
          has_collect: 1,
          collect_num: collect_num,
          can_click: 1,
        })
      } else {
        collect_num--;
        that.setData({
          has_collect: 0,
          collect_num: collect_num,
          can_click: 1,
        })
      }
    });
  },
  showfu: function(e) {
    var hidefu = this.data.hidefu;
    this.setData({
      hidefu: 1
    })
  },
  hidefu: function(e) {
    var hidefu = this.data.hidefu;
    this.setData({
      hidefu: 0
    })
  },
  add: function(e) {
    var num = this.data.num;
    this.setData({
      num: num + 1
    })
  },
  reduce: function(e) {
    var num = this.data.num;
    if (num == 1) {
      this.setData({
        num: 1
      })
    } else {
      this.setData({
        num: num - 1
      })
    }

  },
  previewImage: function(e) {
    var current = e.target.dataset.src;
    var index = e.target.dataset.index;
    var discuss = this.data.discuss;
    wx.previewImage({
      current: current,
      urls: this.data.discuss[index].imglist
    })
  },
  onLoad: function(options) {
    console.log(options);
    var goods_id = options.id;
    if (typeof(options.share_id) == "undefined") {
      wx.setStorageSync("share_id", options.share_id);
    }
    if (options.scene) {
      let scene = decodeURIComponent(options.scene);
      //&是我们定义的参数链接方式      
      let userId = options.scene.split("&")[0];
      if (options.scene) {
        let scene = decodeURIComponent(options.scene);
        //&是我们定义的参数链接方式
        let userId = options.scene.split("&")[0];
        wx.setStorageSync("share_id", userId);
        goods_id = options.scene.split('&')[1];
        //其他逻辑处理。。。。。
      }
      let goods_id = options.scene.split('&')[1];
      //其他逻辑处理。。。。。
    }
    this.setData({
      goods_id: goods_id,
    });
  },

  /**
   * 切换评分
   */
  tab_comment: function(e) {
    console.log(e);
    this.setData({
      cur_comment: e.currentTarget.dataset.index
    });
    getList(this, 1);
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    var that = this;
    var data = {
      goods_id: that.data.goods_id,
    }
    var region = this.data.region;
    app.getData('Home/detail', that, data, function(data) {
      var filter_spec = data.data.filter_spec;
      var cur_spec = "";
      var i = 0;
      for (let key in filter_spec) {
        console.log(filter_spec[key])
        if (i == 0) {
          cur_spec += filter_spec[key][0].item;
        } else {
          cur_spec += ",";
          cur_spec += filter_spec[key][0].item;
        }
        i++;
      }
      console.log(cur_spec)
      var goodsInfo = data.data.goodsInfo;
      that.setData({
        cur_spec: cur_spec,
        stock: goodsInfo.stock,
        price: goodsInfo.price,
        spec_img: goodsInfo.coverimg,
      })
      initGoodsPrice(that);
    })
    getList(this, 1);
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var that = this;
    that.setData({
      can_click: 1,
    });
    app.getData('Home/ajax_cart_num', that, {}, function(data) {
      console.log(data);
    })
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
  onPullDownRefresh: function() {},

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var current = this.data.current;
    if (current == 1) {
      getList(this, 0);
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function(res) {
    console.log(res);
    var user = wx.getStorageSync('user');
    console.log(user);
    var goodsInfo = this.data.goodsInfo;
    if (res.from === 'button') {
      // 来自页面内转发按钮
      console.log(res.target)
    }
    return {
      title: goodsInfo.name,
      path: '/pages/home/detail/detail?id=' + goodsInfo.id + "&share_id=" + user.wx_vip_id,
    }
  }
})
var initGoodsPrice = function(that) {
  var goodsInfo = that.data.goodsInfo;
  var goods_spec_price = that.data.goods_spec_price;
  var goods_spec_arr = [];
  var filter_spec = that.data.filter_spec;
  var filter_spec_arr = Object.keys(filter_spec);

  var item_id = 0;
  var stock = goodsInfo.stock;
  var price = goodsInfo.price;

  console.log(filter_spec_arr);
  for (var i = 0; i < filter_spec_arr.length; i++) {
    for (var k = 0; k < filter_spec[filter_spec_arr[i]].length; k++) {
      if (filter_spec[filter_spec_arr[i]][k].sel == 1) {
        goods_spec_arr.push(filter_spec[filter_spec_arr[i]][k].item_id);
      }
    }
  }
  console.log(goods_spec_arr);
  if (goods_spec_arr.length > 0) {
    var spec_key = goods_spec_arr.sort(sortNumber).join('_'); //排序后组合成 key
    var item_id = goods_spec_price[spec_key]['item_id'];
    var stock = goods_spec_price[spec_key]['store_count'];
    var price = goods_spec_price[spec_key]['price'];
    var spec_img = goodsInfo['coverimg'];
    if (goods_spec_price[spec_key]['spec_img']) {
      spec_img = goods_spec_price[spec_key]['spec_img'];
    }
  }

  that.setData({
    item_id: item_id,
    stock: stock,
    price: price,
    spec_img: spec_img,
  })

  var data = {
    goods_id: that.data.goods_id,
    item_id: item_id,
    goods_num: that.goods_num,
  }
  app.getData('Home/activity', that, data, function(data) {
    console.log(data)
  });
};
var sortNumber = function(a, b) {
  return a - b;
}
var getList = function(that, page) {
  var more_tips = '正在加载中...';
  if (that.data.can_click == 0) return false;
  that.setData({
    can_click: 0,
    more_tips: more_tips,
  })
  var data = {
    loading: 0,
    goods_id: that.data.goods_id,
    star: that.data.cur_comment,
    sort: that.data.sort,
    asc: that.data.asc,
    pagenum: 10,
  }
  // console.log(data)
  app.getList('Home/ajax_comment', that, data, page, function(data) {
    console.log(data)
    if (page == 1 && data.data.length == 0) {
      more_tips = '暂无数据~';
    } else if (data.data.length < 10) {
      more_tips = '没有更多了';
    } else {
      more_tips = '加载更多';
    }
    that.setData({
      can_click: 1,
      more_tips: more_tips,
    })
  })
}