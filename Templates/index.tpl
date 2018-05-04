<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{VAR="page_title"}</title>
    <link rel="stylesheet" type="text/css" href="Style/style.css">
    <script
            src="https://code.jquery.com/jquery-3.3.1.js"
            integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
            crossorigin="anonymous"></script>
    <script
            src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
            integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
            crossorigin="anonymous"></script>
</head>
<body>
{TPL="site_info.tpl"}
<div id="login_information">
    {VAR="log_info"}{GRMR="space"}<a href="?logout=go">{LBL="logout"}</a><br><span id="message">{VAR="message"}</span> <br>
</div>
<div id="allowed_files">
    {LBL="you_can_download"}{GRMR="colon"}<br>

    {CVARE="ext" pre=" (" post=" Mb)<br>"}
    {LBL="used"}{GRMR="colon"}{GRMR="space"}<span id="used_space">{VAR="used_space"}</span>{GRMR="space"}{LBL="megabytes"}{GRMR="space"}{LBL="out_of"}{GRMR="space"}{VAR="all_size"}{GRMR="space"}{LBL="megabytes"}{GRMR="semicolon"}<br>
</div>
{TPL="statistic.tpl"}
<div id="files">
    <table id="table">
        <thead>
        <tr>
            <th>{LBL="filename"}</th>
            <th>{LBL="file_size"}</th>
            <th>{LBL="download"}</th>
            <th>{LBL="delete"}</th>
        </tr>
        </thead>
        <tbody>
        {CVART="table" pre="<tr><td class='smt'>" post="</td>"
        pre="<td>" post="</td>"
        pre="<td><a href='?download=true&file=" post="'> Download</a></td>"
        pre="<td class='delete'><a href='?delete=true&file=" post="'> Delete</a></td></tr>"}
        </tbody>

    </table>

</div>
<div id="main_forms">
    <form action="{CFG="index"}" id="upload" method="post" enctype="multipart/form-data">
        {LBL="file_upload"}<br>
        <input type="file" name="upload_file" id="upload_file">
        <button type="submit">{LBL="submit"}</button>
    </form><br>

    <form action="{CFG="index"}" id="download" method="post">
        {LBL="url_to_upload"}<br>
        <input type="radio" name="location" id="home" value="home" checked>{LBL="home_dir_download"}
        <input type="radio" name="location" id="anywhere" value="anywhere">{LBL="just_download"}<br>
        <input type="text" name="file_to_download" id="download_file"><br>
        <button type="submit">{LBL="submit"}</button>
    </form><br>
</div>

{TPL="footer.tpl"}
<script src="Script/script.js"></script>
</body>
</html>