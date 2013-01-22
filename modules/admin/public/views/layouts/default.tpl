<?php
    // First we setup the menu ID to false
    $a = $b = $c = $d = $e = $f = $g = FALSE;

    // Determine our action to highlight our navigation pane
    switch(strtolower($GLOBALS['controller']))
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
        case "plugins":
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
    
    $Gravatar = new \Library\Gravatar();
    $Gravatar->setAvatarSize(60);
    // $Gravatar->setDefaultImage('{IMG_DIR}/misc/avatar_small.png');
    $Avatar = $Gravatar->get('{session.user.email}');
?>
<!DOCTYPE html>
<head>
    {Plexis::Head}
    
    <!-- Mobile viewport optimized: j.mp/bplateviewport -->
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{IMG_DIR}/icons/favicon.png" />
    
    <!-- Load Stylesheets -->
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/style.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/960.fluid.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/main.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/buttons.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/lists.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/typography.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/forms.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/tables.css" />
    <link rel="stylesheet" type="text/css" href="{CSS_DIR}/charts.css" />
    
    <!-- Load Javascripts -->
    <script type="text/javascript" src="{JS_DIR}/plugins.js"></script>
    <script type="text/javascript" src="{JS_DIR}/modernizr-2.0.6.min.js"></script>
    <script type="text/javascript" src="{JS_DIR}/jquery.uniform.min.js"></script>
    <script type="text/javascript" src="{JS_DIR}/jquery.form.js"></script>
    <script type="text/javascript" src="{JS_DIR}/jquery.tipsy.js"></script>
    <script type="text/javascript" src="{JS_DIR}/jquery.dataTables.js"></script>
    <script type="text/javascript" src="{JS_DIR}/template.js"></script>
</head>
<body id="top">

    <!-- Begin of #container -->
    <div id="container">

        <!-- Begin of #header -->
        <div id="header-surround">
            <header id="header">

                <!-- Logo -->
                <img src="{IMG_DIR}/logo.png" alt="Plexis" class="logo">

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
                <img class="img-left framed" src="<?php echo $Avatar; ?>" alt="Hello Admin">
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
            <nav id="nav" style="padding-bottom: 24px;">
                <ul class="menu collapsible shadow-bottom">
                    <li>
                        <a href="{SITE_URL}/admin" <?php if($a == TRUE) echo 'class="current"';?>>
                        <img src="{IMG_DIR}/icons/small/dashboard.png">Dashboard</a>
                    </li>
                    <li>
                        <a href="{SITE_URL}/admin/users" <?php if($b == TRUE) echo 'class="current"'; ?>>
                        <img src="{IMG_DIR}/icons/small/user.png">Manage Users</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" <?php if($c == TRUE) echo 'class="current"'; ?>>
                        <img src="{IMG_DIR}/icons/small/config.png">Configuration</a>
                        <ul class="sub">
                            <li><a href="{SITE_URL}/admin/settings">Site Settings</a></li>
                            <li><a href="{SITE_URL}/admin/registration">Registration Settings</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" <?php if($d == TRUE) echo 'class="current"'; ?>>
                        <img src="{IMG_DIR}/icons/small/clipboard-list.png">Site Management</a>
                        <ul class="sub">
                            <li><a href="{SITE_URL}/admin/news">News Posts</a></li>
                            <li><a href="{SITE_URL}/admin/groups">User Groups</a></li>
                            <li><a href="{SITE_URL}/admin/modules">Modules</a></li>
                            <li><a href="{SITE_URL}/admin/plugins">Plugins</a></li>
                            <li><a href="{SITE_URL}/admin/templates">Templates</a></li>
                            <li><a href="{SITE_URL}/admin/vote">Vote Sites</a></li>
                            <li><a href="{SITE_URL}/admin/donate">Donation Packages</a></li>
                            <li><a href="{SITE_URL}/admin/shop">Shop System Items</a></li>
                            <li><a href="{SITE_URL}/admin/support">Site Support</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="{SITE_URL}/admin/realms" <?php if($e == TRUE) echo 'class="current"'; ?>>
                        <img src="{IMG_DIR}/icons/small/chart.png">Realm Management</a>
                    </li>
                    <li>
                        <a href="{SITE_URL}/admin/characters" <?php if($f == TRUE) echo 'class="current"'; ?>>
                        <img src="{IMG_DIR}/icons/small/pencil.png">Character Editor</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" <?php if($g == TRUE) echo 'class="current"'; ?>>
                        <img src="{IMG_DIR}/icons/small/system.png">System</a>
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
                        echo \Library\Breadcrumb::GenerateListsOnly("no-hover");
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
                        {Plexis::Messages}
                    </div>

                    <!-- Contents -->
                    {Plexis::Contents}
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
                <p>Page loaded in {Plexis::ElapsedTime} seconds, Using {MEMORY_USAGE}</p>
            </div>
        </footer>

    </div>
    <!--! end of #container -->
</body>
</html>