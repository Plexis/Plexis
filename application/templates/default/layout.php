<!DOCTYPE html> 
<html lang="en" class="no-js">
<head>
    <title>Plexis | Frontend</title>
    <meta charset="utf-8">   
    <meta name="description" content="" />
    <meta name="keywords" content="" />

    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
    <![endif]-->
    <link rel="stylesheet" href="{TEMPLATE_URL}/css/style.css" />
    <link rel="stylesheet" href="{TEMPLATE_URL}/css/header.css" />
    <link rel="stylesheet" href="{TEMPLATE_URL}/css/dialog.css" />
    <link rel="stylesheet" href="{TEMPLATE_URL}/css/lightbox/style.css" class="lb-switch" />
    <link rel="shortcut icon" href="{TEMPLATE_URL}/images/favicon.ico">

    <!-- Modernizr which enables HTML5 elements & feature detects -->
    <script src="{TEMPLATE_URL}/js/modernizr-1.6.min.js"></script>
    <!-- jQuery core -->
    <script src="{TEMPLATE_URL}/js/jquery-1.4.4.min.js"></script>
    <!-- jQuery UI -->
    <script src="{TEMPLATE_URL}/js/jquery-ui-1.8.6.min.js"></script>
    <!-- jQuery easing plugin -->
    <script src="{TEMPLATE_URL}/js/jquery.easing.min.js"></script>
    <!-- jQuery lightbox -->
    <script src="{TEMPLATE_URL}/js/pirobox-min.js"></script>
    <!-- jQuery twitter -->
    <script src="{TEMPLATE_URL}/js/twitter.min.js"></script>
    <!-- jQuery nivo slide -->
    <script src="{TEMPLATE_URL}/js/jquery.nivo.slider.pack.js"></script>
    <!-- jQuery cycle -->
    <script src="{TEMPLATE_URL}/js/jquery.cycle.all.min.js"></script>
    <!-- jQuery custom -->
    <script src="{TEMPLATE_URL}/js/main.js"></script>

</head>
<body>
    <div id="container">

        <!-- Header -->
        <header id="index">
            <div id="header-top">	
                <a href="{SITE_URL}" title="" id="logo"></a>

                <!-- Naviagation -->
                {Compiler:load:partials/navigation.php}
                <!-- End Navigation -->	
            </div>
            
            <!-- Start of slides -->
            <div id="header-mid-index">
                <div id="slider" class="nivoSlider">
                    <img src="{TEMPLATE_URL}/images/slides/slide1.jpg" alt="" />
                    <img src="{TEMPLATE_URL}/images/slides/slide2.jpg" alt="" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent sem justo, ultrices quis consectetur quis, aliquam eu felis."/>
                    <img src="{TEMPLATE_URL}/images/slides/slide3.jpg" alt=""/>
                    <img src="{TEMPLATE_URL}/images/slides/slide4.jpg" alt="" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit sectetur quis, aliquam eu felis." />                                                                             
                </div>             
            </div>
            <!-- End Slides -->

            <div id="header-bottom">
                <h2></h2>
            </div>
        </header>
        <!-- End Header -->
        
        <!-- Contents -->
        <div id="content" class="potfolio infade e-col">   
            <div class="infobox">
                <div id="breadcrumbs">
                    <a href="{SITE_URL}">Home</a> 
                    <?php if($this->_controller != 'welcome') echo ' / '. ucfirst($this->_controller); ?></a> 
                    <?php if($this->_action != 'index') echo ' / '. ucfirst($this->_action); ?>
                </div>
                <p>
                    {GLOBAL_MESSAGES}
                    {PAGE_CONTENTS}
                </p>
            </div>
        </div>
        <!-- End Contents -->

        <!-- Sidebar -->
        {Compiler:load:partials/sidebar.php}
        <!-- END Sidebar -->
        
    </div>
    <!-- END Container -->

    <!-- FOOTER -->
    <footer> 
        <div id="footer">
            <p>
                Copyright &copy; 2011 - ArchDev&trade; 
                <br />Page Rendered in {ELAPSED_TIME} seconds, using {MEMORY_USAGE}
            </p>
        </div> 
    </footer>

    <!-- this is the hidden login panel -->
    <div id="login-box" class="corners">
        <a href="javascript:void(0);" id="login-close"></a>
        <form action="{SITE_URL}/account/login" method="post">
            <h3>Account Login</h3>
            <p>Login in to your account</p>
            <input type="text" onfocus="if(this.value=='Username')this.value='';" onblur="if(this.value=='')this.value='Username';" value="Username" name="username" class="input-1" />
            <input type="password" onfocus="if(this.value=='Password')this.value='';" onblur="if(this.value=='')this.value='Password';" value="Password" name="password" class="input-1" />
            <div class="login-submit">
                <a href="#">Forgot your password?</a><input type="submit" id="login-btn" value="Login" />
            </div> 
        </form>          
    </div>
    <!-- End Hidden Login Panel -->

</body>
</html>