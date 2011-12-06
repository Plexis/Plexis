<!-- dropdown menu -->
<ul id="header-nav" class="corners">
    <li class="sep"><a href="{SITE_URL}" title="">Home</a></li>
    <li class="sep"><a href="{SITE_URL}/forum" title="">Forum</a></li>
    <li class="sep"><a href="{SITE_URL}/account/vote" title="">Vote</a>
    <li class="sep"><a href="{SITE_URL}/account/donate" title="">Donate</a>
    <li class="sep"><a href="{SITE_URL}/server" title="">Server</a>
        <!-- second level dropdown -->
        <ul class="second">
            <li><a href="{SITE_URL}/server/realmist" title="">Realmlist</a></li>
            <li><a href="{SITE_URL}/server/online" title="">Players Online</a></li>
            <li><a href="{SITE_URL}/server/characters" title="">Character list</a></li>
        </ul>
    </li>

    <!-- Account Login -->
    <?php if( $session['user']['logged_in'] == FALSE): ?>
        <li><img src="{TEMPLATE_URL}/images/icons/lock16x16.png" alt="" id="login-down" /></li>
        
    <?php else: ?>
        <li><a href="{SITE_URL}/account" title="">Account</a>
             <!-- second level dropdown -->
            <ul class="second">
                <?php if( {session.user.is_admin} == 1 ): ?>
                    <li><a href="{SITE_URL}/admin/" title="">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="{SITE_URL}/account/" title="">Account Management</a></li>
                <li><a href="{SITE_URL}/account/logout" title="">Logout</a></li>
            </ul>
        </li>
        
    <?php endif; ?>	
    <!-- End Account Login -->
</ul>