<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <title>{$smarty.const.WEB_TITLE}{block name="title"} - {$smarty.const.WEB_DESCRIPTION}{/block}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
        <link rel="shortcut icon" href="{$webDir}assets/favicon.ico">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="{$webDir}assets/css/style.min.css" rel="stylesheet" type="text/css">
        <link href="{$webDir}assets/css/custom.css" rel="stylesheet" type="text/css">
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.1/cookieconsent.min.css" rel="stylesheet" type="text/css">
        {block name="headerInclude"}{/block}
    </head>
    <body>
        <nav class="navbar navbar-custom">
            <div class="container">
                <a href="{route}Dashboard{/route}" class="navbar-brand"><span style="color: #14b3e4;">{$smarty.const.WEB_TITLE}</span> - {$smarty.const.WEB_DESCRIPTION}</a>
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse fix-nav" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li{if $activeRoute == 'Dashboard'} class="active"{/if}>
                            <a href="{route}Dashboard{/route}">Dashboard</a>
                        </li>
                        <li>
                            <a href="https://github.com/IsekaiDev/skadel.net" target="_blank">GitHub</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="subNav">
            <div class="container">
                {* --- *}
            </div>
        </div>

        <div class="main container">
            {block name="content"}
            {/block}
        </div>

        <div class="footer">
            <div class="menu">
                {*<div class="container">
                    <span class="headline">IsekaiDev</span><br />
                    <div class="links">
                        <a href="#">Impressum</a>
                    </div>
                </div>*}
            </div>
            <div class="copyright">
                <div class="container">
                    &copy; {$tplYear} <a href="https://IsekaiDev.de/" target="_blank">{$smarty.const.WEB_TITLE}</a>
                </div>
            </div>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        {block name="footerInclude"}{/block}
    </body>
</html>