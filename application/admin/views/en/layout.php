<?php
    // First we setup the menu ID to false
    $a = $b = $c = $d = $e = $f = $g = FALSE;
    
    // Determine our action to highlight our navigation pane
    switch($GLOBALS['action'])
    {
        case "index":
            $a = TRUE;
            break;
        case "users":
            $b = TRUE;
            break;
        case "settings":
        case "registration":
        case "language":
            $c = TRUE;
            break;
        case "news":
        case "groups":
        case "modules":
        case "templates":
        case "vote":
        case "donate":
        case "shop":
        case "support":
            $d = TRUE;
            break;
        case "realms":
            $e = TRUE;
            break;
        case "characters":
            $f = TRUE;
            break;
        case "console":
        case "update":
        case "errorlogs":
        case "adminlogs":
        case "statistics":
            $g = TRUE;
            break;
    }
?>
<!DOCTYPE html>
<head>
    <pcms::head />
</head>
<body id="top">

    <!-- Begin of #container -->
    <div id="container">
    
        <!-- Begin of #header -->
        <div id="header-surround">
            <header id="header">
            
                <!-- Logo -->
                <img src="{TEMPLATE_URL}/img/logo.png" alt="Plexis" class="logo">
                
                <!-- Update Panel -->
                <div id="update_info" title="Update Information" style="display: none;">
                    <p> 
                        <b>Current Build:</b> @current <br />
                        <b>Lastest Build:</b> @build <br /><br />
                        <b>Latest Commit Message:</b><br />@message<br /><br />
                        <b>Author:</b> @author <br />
                    </p>
                    <br />
                    <div>
                        <center> 
                            <a href="https://github.com/Plexis/Plexis/zipball/master" class="button">Download Zip</a>&nbsp;&nbsp;&nbsp;
                            <a href="{SITE_URL}/admin/update" class="button">Remote Updater</a> 
                        </center>
                    </div>
                </div>
                
                <!-- Begin of #user-info -->
                <div id="user-info">
                    <p>
                        <a href="{SITE_URL}" class="button grey">Return To Site</a> <a href="{SITE_URL}/account/logout" class="button red">Logout</a>
                    </p>
                </div> 
                <!--! end of #user-info -->
            
            </header>
        </div> 
        <!--! end of #header -->
        
        <div class="fix-shadow-bottom-height"></div>
        
        <!-- Begin of Sidebar -->
        <aside id="sidebar">
            
            <!-- Search -->
            <div id="search-bar">
                <form id="search-form" name="search-form" action="search.php" method="post">
                    <input type="text" id="query" name="query" value="" autocomplete="off" placeholder="Search">
                </form>
            </div> 
            <!--! end of #search-bar -->
            
            <!-- Begin of #login-details -->
            <section id="login-details">
                <img class="img-left framed" src="{TEMPLATE_URL}/img/misc/avatar_small.png" alt="Hello Admin">
                <h3>Logged in as</h3>
                <h2><a class="user-button" href="javascript:void(0);"><?php echo ucfirst( strtolower('{session.user.username}')); ?>&nbsp;<span class="arrow-link-down"></span></a></h2>
                <ul class="dropdown-username-menu">
                    <li><a href="{SITE_URL}/account">Manage Account</a></li>
                    <li><a href="{SITE_URL}/account/logout">Logout</a></li>
                </ul>
                
                <div class="clearfix"></div>
            </section> 
            <!--! end of #login-details -->
            
            <!-- Begin of Navigation -->
            <nav id="nav">
                <ul class="menu collapsible shadow-bottom">
                    <li>
                        <a href="{SITE_URL}/admin" <?php if($a == TRUE) echo 'class="current"';?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/dashboard.png">Dashboard</a>
                    </li>
                    <li>
                        <a href="{SITE_URL}/admin/users" <?php if($b == TRUE) echo 'class="current"'; ?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/user.png">Manage Users</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" <?php if($c == TRUE) echo 'class="current"'; ?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/config.png">Configuration</a>
                        <ul class="sub">
                            <li><a href="{SITE_URL}/admin/settings">Site Settings</a></li>
                            <li><a href="{SITE_URL}/admin/registration">Registration Settings</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" <?php if($d == TRUE) echo 'class="current"'; ?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/clipboard-list.png">Site Managment</a>
                        <ul class="sub">
                            <li><a href="{SITE_URL}/admin/news">News Posts</a></li>
                            <li><a href="{SITE_URL}/admin/groups">User Groups</a></li>
                            <li><a href="{SITE_URL}/admin/modules">Modules</a></li>
                            <li><a href="{SITE_URL}/admin/templates">Templates</a></li>
                            <li><a href="{SITE_URL}/admin/vote">Vote Sites</a></li>
                            <li><a href="{SITE_URL}/admin/donate">Donation Packages</a></li>
                            <li><a href="{SITE_URL}/admin/shop">Shop System Items</a></li>
                            <li><a href="{SITE_URL}/admin/support">Site Support</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="{SITE_URL}/admin/realms" <?php if($e == TRUE) echo 'class="current"'; ?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/chart.png">Realm Managment</a>
                    </li>
                    <li>
                        <a href="{SITE_URL}/admin/characters" <?php if($f == TRUE) echo 'class="current"'; ?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/pencil.png">Character Editor</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" <?php if($g == TRUE) echo 'class="current"'; ?>>
                        <img src="{TEMPLATE_URL}/img/icons/small/system.png">System</a>
                        <ul class="sub">
                            <li><a href="{SITE_URL}/admin/statistics">Site Statistics</a></li>
                            <li><a href="{SITE_URL}/admin/adminlogs">Admin Logs</a></li>
                            <li><a href="{SITE_URL}/admin/errorlogs">Error Logs</a></li>
                            <li><a href="{SITE_URL}/admin/console">RA Console</a></li>
                            <li><a href="{SITE_URL}/admin/update">Check For Updates</a></li>
                        </ul>
                    </li>
                </ul>
            </nav> 
        </aside> 
        <!--! end of #sidebar -->
        
        <!-- Begin of #main -->
        <div id="main" role="main">
            
            <!-- Begin of titlebar/breadcrumbs -->
            <div id="title-bar">
                <ul id="breadcrumbs">
                    <li><a href="{SITE_URL}/admin" title="Home"><span id="bc-home"></span></a></li>
                    <?php
                        // Instead of making a custom one in every page, we make an automatic one here
                        if($GLOBALS['action'] == 'index')
                        {
                            echo '<li class="no-hover">Dashboard</li>';
                        }
                        elseif($GLOBALS['action'] !== 'index' && isset($GLOBALS['querystring'][0]))
                        {
                            echo '<li class="no-hover"><a href="{SITE_URL}/'.$GLOBALS['controller'].'/'.$GLOBALS['action'].'">'.ucfirst($GLOBALS['action']).'</a></li>';
                            $__count = count($GLOBALS['querystring']) - 1;
                            foreach($GLOBALS['querystring'] as $k => $qs)
                            {
                                if($__count == $k) {
                                    echo '<li class="no-hover">'.ucfirst($qs).'</li>';
                                }
                                else
                                {
                                    $__string = '<li><a href="{SITE_URL}/'.$GLOBALS['controller'].'/'.$GLOBALS['action'];
                                    for($i = 0; $i <= $k; $i++)
                                    {
                                        $__string .= '/'. $GLOBALS['querystring'][$i];
                                    }
                                    echo $__string .'">'.ucfirst($qs).'</a></li>';
                                }
                            }
                        }
                        else
                        {
                            echo '<li class="no-hover">'.ucfirst($GLOBALS['action']).'</li>';
                        }
                    ?>
                </ul>
            </div> 
            <!--! end of #title-bar -->
            
            <div class="shadow-bottom shadow-titlebar"></div>
            
            <!-- Begin of #main-content -->
            <div id="main-content">
                <div class="container_12">
                     <!-- Page Description -->
                    <div class="grid_12">
                        <h1>{page_title}</h1>
                        <p>{page_desc}</p>
                    </div>
                    
                    <!-- Global Messages -->
                    <div class="grid_12">
                        <pcms::global_messages />
                    </div>
                
                    <!-- Contents -->
                    <pcms::page_contents />
                </div> 
                <div class="clear height-fix"></div>
            </div>
            <!--! end of #main-content -->
            
        </div> 
        <!--! end of #main -->

        <!-- Footer -->
        <footer id="footer">
            <div class="left">
                Plexis CMS &copy; 2012, Plexis Organization<a id="open-info-dialog" href="javascript:void(0);"><span class="btn-info"></span></a>
            </div>
            <div class="right">
                <a href="#top" title="Scroll to Top"><span class="to-top"></span></a>
            </div>
            
            <!-- Info dialog -->
            <div id="cms-info-dialog" title="Plexis CMS" style="display: none;">
                <p>
                    Plexis is a powerful content management system for WoW Servers.
                </p>
                <p>Page loaded in {ELAPSED_TIME} seconds, Using {MEMORY_USAGE}</p>
            </div>
        </footer>

    </div> 
    <!--! end of #container -->
</body>
</html>