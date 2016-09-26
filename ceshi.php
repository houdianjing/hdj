<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Title</title>
    <script type="text/javascript" src="./js/jquery.min.js"></script>
    <script type="text/javascript">
        $(function () {
            $('#chuan').click(function () {
               // var a=$('#a').val();
                var type=$('#type').val();
                var keywords=$('#keywords').val();
               $.post('./interface/search_api.php',{'keywords':keywords,'action':'search','type':type},function (date) {
                    //  var dataObj=eval("("+date+")");
                    //alert(dataObj.info);
                    alert(date.msg);
                },'json')
            })
        })
    </script>
</head>
<body>
<form action="#" method="post" name="myform">
    <input type="text" id="type" value="">
    <input type="text" id="keywords" value="">
    <input type="button" value="传值" id="chuan"/>
</form>

</body>
</html>