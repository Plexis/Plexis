<div class="grid_8">
    <div class="block-border">
        <div class="block-header">
            <h1>Remote Updater</h1><span></span>
        </div>
        <div class="block-content">
            <!-- Update details -->
            <div id="details">
                <div class="alert success">Your CMS is up to date!</div>
                <br />
                <h2>Current Version:</h2>
                <table id="update-table" class="table">
                    <tbody>
                        <tr>
                            <td width="20%">Build Message:</td>
                            <td id="current_build">{message}</td>
                        </tr>
                        <tr>
                            <td>Commited By:</td>
                            <td>{author}</td>
                        </tr>
                        <tr>
                            <td>Commit Date / Time:</td>
                            <td>{date}</td>
                        </tr>
                    </tbody>
                </table>
                <br />
                <small><center>Github Response Time: {time} seconds</center></small>
            </div>
        </div>
    </div>
</div>

<div class="grid_4">
    <div class="block-border">
        <div class="block-header">
            <h1>Update Information</h1><span></span>
        </div>
        <div class="block-content">
            <table id="data-table" class="table">
                <tbody>
                    <tr>
                        <td width="50%">Current Build:</td>
                        <td id="current_build">{CMS_BUILD}</td>
                    </tr>
                    <tr>
                        <td>Latest Build:</td>
                        <td>{latest}</td>
                    </tr>
                    <tr>
                        <td>View Commit on Github:</td>
                        <td><a href="{more_info}" target="_blank">More Info</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>