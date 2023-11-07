<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPMYADMIN</title>
</head>
<body>
    <form action="http://yadmin.qyrdata.com:888/phpmyadmin_756304a8b669512f" method="get">
        <p>说明：这里的输入表单数据，就相当于到时从MYSQL数据库查询出来的数据库信息</p>
        <p>跳转过去的PHPMYADMIN到时可以在后台显示，也可以新窗口打开</p>
        <label for="">IP</label>
        <input type="hidden" name="auth_type" value="config">
        <P><input type="text" name="host"></P>
        <label for="">UserName</label>
        <P><input type="text" name="user"></P>
        <label for="">Password</label>
        <P><input type="text" name="password"></P>
        <label for="">Port</label>
        <P><input type="text" name="port"></P>
        <label for="">Db</label>
        <P><input type="text" name="db"></P>
        <button type="submit">Submit</button>
    </form>
</body>
</html>