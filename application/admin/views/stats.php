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