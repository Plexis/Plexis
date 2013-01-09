<?php defined('ROOT') or die('No Direct Access Allowed!'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title>{title} :: 404 - Not Found</title>
	<style type="text/css">
		@font-face {
			font-family: Square;
			src: url( '{site_url}/{root_dir}/fonts/Square.ttf' );
		}
        html {
           min-height: 100%;
        }
        body
        {
            background: #454545;
            padding: 30px;
            margin: 0;
        }
        #container {
            margin: auto;
            width: 800px;
            text-align: center;
        }

        #error-number h1 {
            font-family: Square;
            color: #E8E8E8;
            font-size: 200px;
            font-weight: 900;
            text-shadow: 1px 1px 0 #D3D3D3, -1px -1px 0 #D3D3D3, -1px 1px 0 #D3D3D3, 1px -1px 0 #D3D3D3,
                         2px 2px 0 #494949, -2px -2px 0 #494949, -2px 2px 0 #494949, 2px -2px 0 #494949,
                         3px 3px 0 #5D5D5D, -3px -3px 0 #5D5D5D, -3px 3px 0 #5D5D5D, 3px -3px 0 #5D5D5D;
            line-height: 20px;
        }

        #message {
            font-family: Tahoma, Arial;
            color: #E8E8E8;
            text-shadow: 0px 1px 0px #200404;
            margin:10px;
            font-size: 16px;
        }

        #links {
            padding-top: 3px;
            margin:10px;
            text-align: center;
            font-size: 12px;
        }

        a:link {
            color: #C8C8C8;
        }

        a:visited {
            color: #C8C8C8;
        }

        a:hover {
            color: #FFF;
        }

        a:active {
            text-decoration: none;
        }
	</style>
</head>
<body>
	<div id="container">
		<section id="error-number">
            <h1>404</h1>
        </section>
		<section id="message">
			Sorry, the page you are looking for cannot be located. You may have mis-typed the URL, or the page was deleted. 
			Please check your spelling and try again. If you feel you have reached this page in error, please contact the 
			server administrator.<br /><br />
		</section>
		<section id="links">
            <img src="{site_url}/{root_dir}/img/border.png"><br /><br />
			<a href='<?php echo $site_url; ?>'>Return to Index</a> | <a href='javascript: history.go(-1)'>Previous Page</a>
		</section>
	</div>
</body>
</html>