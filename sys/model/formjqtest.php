
<SCRIPT language=JavaScript>  
$("document").ready(function(){  
    //第六个表格的删除按钮事件绑定   
    $("#tbody6 .del").click(function() {   
        $(this).parents(".repeat").remove();   
    });   
    //第六个表格的添加按钮事件绑定   
    $("#add6").click(function(){   
        $("#tbody6>.template")   
            //连同事件一起复制   
            .clone(true)   
            //去除模板标记   
            .removeClass("template")   
            //修改内部元素   
            .find(".content")   
                .text("新增行")   
                .end()  
           .find(".del")   
                .text("删除")   
                .end()        
            //插入表格   
            .appendTo($("#tbody6"))   
    });   
}  
)  
</script>  
  
    <table border=1 id="table6">   
    <tbody id="tbody6">   
        <tr class="template repeat">   
            <td class="content">这里是模板 by yanleigs Email:landgis@126.com</td>   
            <td><button class="del">模板,不要删除</button></td>   
        </tr>   
        <tr class="repeat">   
            <td class="content">这行原来就有</td>   
            <td><button class="del">删除</button></td>   
        </tr>   
        <tr class="repeat">   
            <td class="content">这行原来就有</td>   
            <td><button class="del">删除</button></td>   
        </tr>   
    </tbody>   
    <tfoot>   
        <tr>   
            <td> </td>   
            <td><button id="add6">添加</button></td>   
        </tr>   
    </tfoot>   
</table>   
  