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
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_URL}/css/dialog.css" />
    
    <!-- Scripts -->
    <script type="text/javascript" src="{TEMPLATE_URL}/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="{TEMPLATE_URL}/js/jquery.cycle.all.min.js"></script>
    <script type="text/javascript">
    <!--
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
    -->
    </script>
    <script src="{TEMPLATE_URL}/js/dropdown.js" type="text/javascript"></script>
</head>
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
                    <li><a href="{SITE_URL}/support">Server</a>
                        <ul class="subnav">
                            <li><a href="{SITE_URL}/server/realmlist">Realmlist</a></li>
                            <li><a href="{SITE_URL}/server/online">Players Online</a><span class="spmore"></span>
                                <ul class="subnav-out">
                                    <li><a href="#">Test</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><a href="{SITE_URL}/account/register">Support</a>
                    
                    <!-- Account Login -->
                    <?php if( $session['user']['logged_in'] == FALSE): ?>
                        <li><a href="{SITE_URL}/account/register">Register</a>
                        
                    <?php else: ?>
                        <li><a href="#">Account</a>
                        <ul class="subnav">
                            <li><a href="{SITE_URL}/account">Dashboard</a></li>
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
                            <li>Realm 1</li>
                            <li>Realm 2</li>
                            <li>Realm 3</li>
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