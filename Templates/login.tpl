<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{VAR="page_title"}</title>
    <link rel="stylesheet" type="text/css" href="Style/style.css">
</head>
<body>
{TPL="site_info.tpl"}
<div id="login_information">
    {VAR="log_info"}<br><span id="message">{VAR="message"}</span><br>
</div>
{TPL="statistic.tpl"}
<div id="login_form">
    <form action="{CFG="index"}" method="post">
        {LBL="username"}{GRMR="colon"}<br>
        <input name="username"><br>
        {LBL="password"}{GRMR="colon"}<br>
        <input type="password" name="password"><br>
        {LBL="remember_me"}<input type="checkbox" name="remember" value="remember"><br>
        <button type="submit" name="login">Login</button>
    </form>
</div>

{TPL="footer.tpl"}
</body>
</html>