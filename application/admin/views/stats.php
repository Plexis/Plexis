<div class="grid_12">
    <div class="block-border" id="tab-graph">
        <div class="block-header">
            <h1>Accounts Registered</h1>
            <ul class="tabs">
                <li><a href="#tab-area">Area</a></li>
                <li><a href="#tab-line">Line</a></li>
                <li><a href="#tab-bar">Bar</a></li>
            </ul>
        </div>
        <div class="block-content tab-container">
            <table id="graph-data" class="graph">
                <caption>Accounts created over the last 6 months</caption>
                <thead>
                    <tr>
                        {months}
                            <th scope="col">{name}</th>
                        {/months}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        {months}
                            <td>{value}</td>
                        {/months}
                    </tr>		
                </tbody>
            </table>
            
            <div id="tab-area" class="tab-content"></div>
            <div id="tab-line" class="tab-content"></div>
            <div id="tab-bar" class="tab-content"></div>
            
        </div>
    </div>
</div>

<div class="grid_6">
    <div class="block-border">
        <div class="block-header">
            <h1>Account Stats</h1><span></span>
        </div>
        <div class="block-content">
            <ul class="overview-list">
                <li><a href="javascript:void(0);"><span>{account_count}</span> Registered Users</a></li>
                <li><a href="javascript:void(0);"><span>{inactive_accounts}</span> Inactive Accounts (Not Logged in (Game) within last 90 Days)</a></li>
                <li><a href="javascript:void(0);"><span>{accounts_banned}</span> Banned Accounts</a></li>
                <li><a href="javascript:void(0);"><span>{accounts_active}</span> Users Online (Site) in the Last 24 Hours</a></li>
                <li><a href="javascript:void(0);"><span>{active_accounts}</span> Users Logged In (Game) in the Last 24 Hours</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="grid_6">
    <div class="block-border">
        <div class="block-header">
            <h1>Website Stats</h1><span></span>
        </div>
        <div class="block-content">
            <ul class="overview-list">
                <li><a href="javascript:void(0);"><span>{hits.today}</span> Unique Vists (Last 24 Hours)</a></li>
                <li><a href="javascript:void(0);"><span>{hits.unique}</span> Unique Visits (Total)</a></li>
            </ul>
        </div>
    </div>
</div>