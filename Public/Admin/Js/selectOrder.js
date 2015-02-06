
function changeStatus()
{
    var id = document.getElementById("orderId").value;
    var status = document.getElementById("statusChange").value;
    $.ajax({
        url:"/index.php/Shop/Order/changeStatus",
        type:"post",
        data:{
            orderId : id,
            status : status
        },
        dataType:'json',
        success:function(msg){
            if(msg == 200)
            {
                if(confirm("更新成功"))
                {
                    var orderId = document.getElementById("orderId").value;
                    $("#order").val(orderId);
                    $("#order1").val(orderId);
                    document.getElementById('orderInfoFrm').submit();
                }
            }else
            {
                alert("操作失败");
            }
        }
    });
}

function confirmUserImage(type)
{
    var id = document.getElementById("orderId").value;
    var status = 0;
    if(type == 0)
    {
        status = 8;
    }else
    {
        status = 2;
    }
    $.ajax({
        url:"/index.php/Shop/Order/changeStatus",
        type:"post",
        data:{
            orderId : id,
            status : status
        },
        dataType:'json',
        success:function(msg){
            if(msg == 200)
            {
                if(confirm("更新成功"))
                {
                    var orderId = document.getElementById("orderId").value;
                    $("#order").val(orderId);
                    $("#order1").val(orderId);
                    document.getElementById('orderInfoFrm').submit();
                }
            }else
            {
                alert("操作失败");
            }
        }
    });
}

function changePostNum(id)
{
    var order = document.getElementById("orderId");
    order.value = id;
    easyDialog.open({
        container : 'easyDialogWrapper',
        autoClose : false,
        fixed : false
    });
}
function sendGood(id)
{
    var order = document.getElementById("orderId");
    order.value = id;
    easyDialog.open({
        container : 'easyDialogWrapper',
        autoClose : false,
        fixed : false
    });
}

function change()
{
    var id = document.getElementById("orderId").value;
    var type = document.getElementById("type_"+id).value;
    var post = document.getElementById("post").value;
    $.ajax({
        url:"/index.php/Shop/Order/addPostNum",
        type:"post",
        data:{
            orderId : id,
            type : type,
            post : post
        },
        dataType:'json',
        success:function(msg){
            if(msg == 200)
            {
                if(confirm("操作成功"))
                {
                    window.location.href = '/index.php/Shop/Order/orderInfo';
                }
            }else
            {
                alert("操作失败！");
                return false;
            }
        }
    });

}

function addPost(id)
{
    var orderId = Number(id);
    var type = document.getElementById("type_"+id).value;
    alert(type);
}


function preview(oper)
{
    if (oper < 10){
        bdhtml=window.document.body.innerHTML;//获取当前页的html代码
        sprnstr="<!--startprint"+oper+"-->";//设置打印开始区域
        eprnstr="<!--endprint"+oper+"-->";//设置打印结束区域
        prnhtml=bdhtml.substring(bdhtml.indexOf(sprnstr)+18); //从开始代码向后取html
        prnhtml=prnhtml.substring(0,prnhtml.indexOf(eprnstr));//从结束代码向前取html
        window.document.body.innerHTML=prnhtml;
        window.print();
        window.document.body.innerHTML=bdhtml;
    } else {
        window.print();
    }
}

function addPostInfo()
{
    var orderId = document.getElementById("orderId").value;
    var type = document.getElementById("type").value;
    var post = document.getElementById("post").value;
    $.ajax({
        url:"/index.php/Shop/Order/addPostNum",
        type:"post",
        data:{
            orderId : orderId,
            type : type,
            post : post
        },
        dataType:'json',
        success:function(msg){
            if(msg == 200)
            {
                if(confirm("操作成功"))
                {
                    var orderId = document.getElementById("orderId").value;
                    $("#order").val(orderId);
                    $("#order1").val(orderId);
                    document.getElementById('orderInfoFrm').submit();
                }
            }else
            {
                alert("操作失败！");
                var orderId = document.getElementById("orderId").value;
                $("#order").val(orderId);
                $("#order1").val(orderId);
                document.getElementById('orderInfoFrm').submit();
            }
        }
    });
}

//function packageZip()
//{
//    var orderId = document.getElementById("orderId").value;
//    $.ajax({
//        url:"/index.php/Shop/Order/packageZip",
//        type: "post",
//        data: {
//            orderId : orderId
//        },
//        dataType:'json',
//        success:function(msg)
//        {
//            var json_info = eval(msg);
//            if(json_info['status'] == 200)
//            {
//                alert(200);
//
//            }else
//            {
//                alert(500);
//            }
//        }
//
//    })
//}
