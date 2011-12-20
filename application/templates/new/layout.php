<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title> <?php echo config('site_title'); ?> </title>
    <meta name="author" content="" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    
    <!-- Style Sheet -->
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_URL}/css/style.css" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_URL}/css/alerts.css" />
    
    <!-- Scripts -->
    <script type="text/javascript" src="{TEMPLATE_URL}/js/jquery-1.6.2.min.js"></script>
    <script type="text/javascript" src="{TEMPLATE_URL}/js/jquery.cycle.all.min.js"></script>
    <script type="text/javascript" src="{TEMPLATE_URL}/js/jquery.validate.min.js"></script>
    <script type="text/javascript">
    <!--
        // Slide Show
        jQuery(function( $ ){
            $('#slide') 
            .after('<div id="slide-tabs">') 
            .cycle({ 
                fx:     'fade', 
                speed:  'slow', 
                timeout: 4500, 
                pager:  '#slide-tabs'
            });
        });
        
        // Setup our form validator error style class
        jQuery.validator.setDefaults({ 
            errorClass: "input-error",
        });
    -->
    </script>
    {VIEW_JS}
</head>

<!-- Thie template doesnt work for Internet Explorer, so we show a message to IE users -->
<div style="position:fixed;background-color:#eee;width:100%;height:100%;z-index:99999;display:none;text-align:center;font-size:24px;color:#333;padding-top:10%" id="ie">
	<h1 style="margin-bottom:5px;font-size:52px;">Dear Internet Explorer user...</h1>
	<h2 style="font-size:40px; margin-top: 100px;">Your browser is not supported!</h2>
	<h2 style="font-size:20px;margin-top:30px;">Please upgrade to a more modern browser. These are our recommendations:</h2>

	<div style="margin-top:30px;">
		<a href="http://www.google.com/chrome"><img src="{TEMPLATE_URL}/images/ie_splash/chrome.jpg" /></a>
		<a href="http://www.mozilla.org/firefox/"><img src="{TEMPLATE_URL}/images/ie_splash/firefox.jpg" /></a>
		<a href="http://www.apple.com/safari/"><img src="{TEMPLATE_URL}/images/ie_splash/safari.jpg" /></a>
		<a href="http://www.opera.com/"><img src="{TEMPLATE_URL}/images/ie_splash/opera.jpg" /></a>
	</div>
</div>
	
<!--[if IE]>
<script type="text/javascript">
	$("#ie").show()
</script>
<![endif]-->

<body>
    <div id="container">

        <div id="header">
            <div id="logo"><a href="#"><img src="{TEMPLATE_URL}/images/logo.jpg" alt="Our Website" /></a></div>
        </div><!-- /header -->

        <div id="content-container">
            <div id="nav">
                <ul class="navigation">
                    <li><a href="{SITE_URL}">Home</a></li>
                    <li><a href="{SITE_URL}/forum">Forums</a></li>
                    <li><a href="{SITE_URL}/account/vote">Vote</a></li>
                    <li><a href="{SITE_URL}/account/donate">Donate</a></li>
                    <li><a href="{SITE_URL}/server">Server</a>
                        <ul class="subnav">
                            <li><a href="{SITE_URL}/server/realmlist">Realmlist</a></li>
                            <li><a href="{SITE_URL}/server/onlinelist">Players Online</a>
                                <?php
                                    $realms = get_installed_realms();
                                    if( !empty($realms) )
                                    {
                                        echo '<span class="spmore"></span>';
                                        echo '<ul class="subnav-out">';
                                        foreach($realms as $realm)
                                        {
                                            echo '<li><a href="'.SITE_URL.'/server/onlinelist/'.$realm['id'].'">'.$realm['name'].'</a></li>';
                                        }
                                        echo '</ul>';
                                    }
                                ?>
                            </li>
                            </li>
                        </ul>
                    </li>
                    <li><a href="{SITE_URL}/support">Support</a>
                        <ul class="subnav">
                            <li><a href="{SITE_URL}/support/howtoplay">Connection Guide</a></li>
                        </ul>
                    </li>
                    
                    <!-- Account Login -->
                    <?php if( $session['user']['logged_in'] == FALSE): ?>
                        <li><a href="{SITE_URL}/account/register">Register</a></li>
                        
                    <?php else: ?>
                        <li><a href="#">Account</a>
                            <ul class="subnav">
                                <li><a href="{SITE_URL}/account">Dashboard</a></li>
                                <li><a href="{SITE_URL}/account/update/password">Change Password</a></li>
                                <li><a href="{SITE_URL}/account/update/email">Update Email</a></li>
                                <li><a href="{SITE_URL}/account/logout">Logout</a></li>
                            </ul>
                        </li>
                        
                    <?php endif; ?>	
                    <!-- End Account Login -->
                    
                    <!-- Admin -->
                    <?php if( $session['user']['is_admin'] == TRUE || $session['user']['is_super_admin'] == TRUE): ?>
                        <li><a href="{SITE_URL}/admin">Admin Panel</a>
                    <?php endif; ?>

                </ul>
            </div><!-- /navigation -->

            <div id="main" class="clearfix">
                <div id="right">
                    
                    <!-- Account Login -->
                    <?php if( $session['user']['logged_in'] == FALSE): ?>
                        <div class="right-box">
                            <h3>Login / Register</h3>
                            <p>
                                <form method="post" action="{SITE_URL}/account/login" id="form">		
                                    <fieldset class="login-right">	
                                        <label for="username" class="top-label">Username:</label> 
                                        <input type="text" name="username" id="username" value="" size="28" tabindex="10" />
                                        
                                        <label for="password" class="top-label">Password:</label> 
                                        <input type="password" name="password" id="password" value="" size="28" tabindex="11" />
                                        
                                        <center>
                                            <input type="submit" name="submit" value="Login" class="button" tabindex="12"/>
                                            <input type="button" class ="button" name="register" value="Register" onClick="window.location='{SITE_URL}/account/register'" tabindex="13">
                                            <br />
                                            <small><a href="{SITE_URL}/account/recover">Recover lost password</a></small>
                                        </center>
                                    </fieldset>
                                </form>
                            </p>
                        </div><!-- /right-box -->  
                    <?php else: ?>
                        <div class="right-box">
                            <h3>Account</h3>
                            <p>
                                <center>Welcome {session.user.username}!</center>
                            </p>
                        </div><!-- /right-box -->
                    <?php endif; ?>	
                    <!-- End Account Login -->

                    <div class="right-box">
                        <h3>Realm Status</h3>
                        <ul>
                            <?php
                                $realms = get_realm_status();
                                foreach($realms as $r)
                                {
                                    if($r['status'] == 1)
                                    {
                                        $text = "<font color='green'>Online</font>";
                                    }
                                    else
                                    {
                                        $text = "<font color='red'>Offline</font>";
                                    }
                                    
                                    // Echo out the information
                                    echo '<li><center><b><a href="{SITE_URL}/server/realm/'.$r['id'].'">'.$r['name'].'</a> - '.$text.'</b></center></li>';
                                }
                            ?>
                            <li><center><small><a href="{SITE_URL}/support/howtoplay">Connection Guide</a></small></center></li>
                        </ul>
                    </div><!-- /right-box -->
                </div><!-- /right -->
                
                <!-- MAIN CONTENT -->
                <div id="left">
                    <?php if($GLOBALS['controller'] == 'welcome'): ?>
                        <!-- Slide Show -->
                        <div id="slide">
                            <div class="slide-image"><a href=""><img src="{TEMPLATE_URL}/images/feature-1.jpg" alt="" /></a><p>Plexis, A Professional WoW CMS!</p></div>
                            <div class="slide-image"><a href=""><img src="{TEMPLATE_URL}/images/feature-2.jpg" alt="" /></a><p>Awesome!</p></div>
                            <div class="slide-image"><a href=""><img src="{TEMPLATE_URL}/images/feature-4.jpg" alt="" /></a></div>
                        </div>
                        <!-- /Slide -->
                    <?php endif; ?>
                    
                    {GLOBAL_MESSAGES}
                    {PAGE_CONTENTS}
                </div>
                <!-- /MAIN CONTENT -->

            </div>
            <!-- /main -->
            
            <!-- FOOTER -->
            <div id="footer">
                <p id="footer-left">&copy; 2011 Plexis.</p>
                <p id="footer-right">Page Rendered in {ELAPSED_TIME} seconds, Using {MEMORY_USAGE}</p>
            </div>
            <!-- END FOOTER -->
        </div>
    </div>
    <!-- /container -->
</body>
</html>