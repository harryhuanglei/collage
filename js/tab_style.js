// 页面列表橱窗样式切换
var tuanList = true;
function tabStyle(obj){
    if(tuanList){       
        $(obj).css("background-image","url(themes/haohainew/images/category_list_btn_act.png)");
        //tuan
        $(".tuan_list").addClass("tuan-tab-hide");
        $(".like_click_button , .tuan_g_img_text").hide();
        $(".tuan_g_btn").text("开团");
        //mall
        $(".list_B").addClass("mall-tab-hide");
        $(".mall-tab-hide").find(".mall-tab-a").addClass("mall-tab-link");
        $(".mall-tab-tit").addClass("mall-tab-price");
        tuanList = false;
    }else{
        $(obj).css("background-image","url(themes/haohainew/images/category_list_btn.png)");
        //tuan
        $(".tuan_list").removeClass("tuan-tab-hide");
        $(".like_click_button , .tuan_g_img_text").show();
        $(".tuan_g_btn").text("立即开团");
        //mall
        $(".list_B").removeClass("mall-tab-hide");
        tuanList = true;
    }  
}