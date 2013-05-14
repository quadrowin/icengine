<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="/cache/cssAdmin.css?{$time}" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="/cache/jsAdmin.js?{$time}"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Авторизация</title>
</head>
<body>
    <style>
        .main:after {
            background: url("http://twitter.github.com/bootstrap/assets/img/bs-docs-masthead-pattern.png") repeat scroll center center transparent;
            bottom: 0;
            content: "";
            z-index:0;
            display: block;
            left: 0;
            opacity: 0.4;
            position: absolute;
            right: 0;
            top: 0;
        }
    </style>    
{$content}
    {$helperViewResource->embedJs()}
    {$helperViewResource->embedCss()}
    {$helperViewResource->embedJtpl()}
</body>
</html>