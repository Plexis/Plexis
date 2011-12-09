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
    <script type="text/javascript" src="{TEMPLATE_URL}/scripts/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="{TEMPLATE_URL}/scripts/jquery.cycle.all.min.js"></script>
    <script type="text/javascript">
    <!--
        jQuery(function( $ ){
            $('#featured') 
            .after('<div id="featured-tabs">') 
            .cycle({ 
                fx:     'fade', 
                speed:  'slow', 
                timeout: 3500, 
                pager:  '#featured-tabs'
            });
        });
    -->
    </script>
    <script src="{TEMPLATE_URL}/scripts/dropdown.js" type="text/javascript"></script>
</head>
<body>
    <div id="container">

        <div id="header">
            <div id="logo"><a href="#"><img src="{TEMPLATE_URL}/images/logo.jpg" alt="Our Website" /></a></div>
        </div><!-- /header -->

        <div id="content-container">
            <div id="navigation">
                <ul>
                    <li><a href="{SITE_URL}">Home</a></li>
                    <li><a href="{SITE_URL}/forum">Forums</a></li>
                    <li><a href="{SITE_URL}/account/vote">Vote</a></li>
                    <li><a href="{SITE_URL}/account/donate">Donate</a></li>
                    <li><a href="{SITE_URL}/server">Server</a>
                        <ul>
                            <li><a href="{SITE_URL}/server/realmlist">Realmlist</a></li>
                            <li><a href="{SITE_URL}/server/online">Players Online</a></li>
                        </ul>
                    </li>
                    
                    <!-- Account Login -->
                    <?php if( $session['user']['logged_in'] == FALSE): ?>
                        <li><a href="{SITE_URL}/account/register">Register</a>
                        
                    <?php else: ?>
                        <li><a href="#">Account</a>
                        <ul>
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
                                    <fieldset>	
                                        <label for="username">Username</label> <input type="text" name="username" id="username" value="" tabindex="1" />
                                        <label for="password">Password</label> <input type="password" name="password" id="password" value="" tabindex="2" />
                                        <input type="submit" name="submit" value="Login" class="send" tabindex="3" />
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
                
                <div id="left">
                    <?php if($GLOBALS['controller'] == 'welcome'): ?>
                        <!-- Slide SHow -->
                        <div id="featured">
                            <div class="featured-images"><a href="portfolio.html"><img src="{TEMPLATE_URL}/images/pic1.jpg" alt="" /></a><p>Planet Earth: The Sky Html Template</p></div>
                            <div class="featured-images"><a href="portfolio.html"><img src="{TEMPLATE_URL}/images/pic2.jpg" alt="" /></a><p>Domates One Page Portfolio Template</p></div>
                            <div class="featured-images"><a href="portfolio.html"><img src="{TEMPLATE_URL}/images/pic3.jpg" alt="" /></a><p>Renkli WordPress Theme</p></div>
                            <div class="featured-images"><a href="portfolio.html"><img src="{TEMPLATE_URL}/images/pic2.jpg" alt="" /></a><p>Domates One Page Portfolio Template</p></div>
                        </div><!-- /featured -->
                    <?php endif; ?>
                    
                    {GLOBAL_MESSAGES}
                    {PAGE_CONTENTS}
                </div><!-- /left -->
                
                
            </div><!-- /main -->
            <div id="footer">
                <p id="footer-left">&copy; 2011 Plexis.</p>
                <p id="footer-right">Page Rendered in {ELAPSED_TIME} seconds, Using {MEMORY_USAGE}</p>
            </div>
        </div>
    </div><!-- /container -->
</body>
</html>