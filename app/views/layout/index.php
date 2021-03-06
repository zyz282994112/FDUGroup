<?php
    $baseurl = Rays::app()->getBaseUrl();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title><?php echo RHtmlHelper::encode(Rays::app()->getClientManager()->getHeaderTitle()); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="language" content="en"/>
    <meta name="description" content=""/>

    <link rel="stylesheet" type="text/css" href="<?php echo $baseurl; ?>/public/bootstrap-3.0/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $baseurl; ?>/public/css/main.css"/>
    <?php
        // link custom css files
        echo RHtmlHelper::linkCssArray(Rays::app()->getClientManager()->css);
    ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <script type="text/javascript" src="<?php echo $baseurl; ?>/public/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $baseurl; ?>/public/bootstrap-3.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo $baseurl; ?>/public/js/main.js"></script>

</head>

<body>
<?php $this->module('main_nav',array('id'=>'main_nav','name'=>'Main navigation')); ?>

<div class="container">

    <div class="row row-offcanvas row-offcanvas-right">
        <div class="col-xs-12 col-sm-9">
            <p class="pull-right visible-xs">
                <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
            </p>
            <div id="messages"><?=RHtmlHelper::showFlashMessages()?></div>
            <div id="content">
                <?php if(isset($content)) echo $content; ?>
            </div>

        </div><!--/span-->

        <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">

                    <?php

                    $this->module("help_nav",array('id'=>'help_nav'));

                    $this->module("group_categories",array('id'=>'group_categories','name'=>"Categories"));
                    $this->module("friend_groups",array('id'=>'friend_groups','name'=>"Friend Groups"));
                    $this->module("friend_users",array('id'=>'friend_users','name'=>"Friends"));
                    $this->module("group_users",array('id'=>'group_users','name'=>"Group Users"));
                    $this->module("new_users",array('id'=>'new_users','name'=>"New Users"));
                    $this->module("ads",array('id'=>'ads','name'=>"Ads"));

                    ?>
            <!--
            <div class="well sidebar-nav">
                <ul class="nav">
                    <li>Sidebar</li>
                    <li class="active"><a href="#">Link</a></li>
                    <li><a href="#">Link</a></li>
                    <li><a href="#">Link</a></li>
                    <li>Sidebar</li>
                    <li><a href="#">Link</a></li>
                    <li><a href="#">Link</a></li>
                    <li><a href="#">Link</a></li>
                </ul>
            </div>--><!--/.well -->
        </div><!--/span-->
    </div><!--/row-->
    <hr>
    <footer>
        <p><?php echo RHtmlHelper::encode(Rays::getCopyright()); ?></p>
    </footer>

</div><!--/.container-->

<!-- Placed at the end of the document so the pages load faster -->
<?php
    // link custom script files
    echo RHtmlHelper::linkScriptArray(Rays::app()->getClientManager()->script);
?>
</body>

</html>