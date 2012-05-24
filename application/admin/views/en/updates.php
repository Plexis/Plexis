<div class="grid_8">
    <div class="block-border">
        <div class="block-header">
            <h1>Remote Updater</h1><span></span>
        </div>
        <div class="block-content">
            <div class="alert warning">Please note this is still in development and may cause errors!</div>
            
            <!-- Progess of update -->
            <div id="update" style="display: none; padding: 25px;">
                <p><center>Updating... Please do not refresh the screen!</center></p>
                <div id="progressbar"></div>
                <br />
                <div id="update-state"></div>
            </div>
            
            <!-- Update details -->
            <div id="details">
                <br />
                <b><u><font color="green"><center>There are {count} Updates available!</center></font></u></b>
                <br />
                <h2>Next Update:</h2>

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
                <center><a id="update-cms" class="button" href="javascript:void(0);">Update CMS</a></center>
                <br />
                <small><center>Github Response Time: {time} seconds</center></small>
            </div>
            
             <!-- End of update -->
            <div id="update-finished" style="display: none;">
                <p>
                    <div class="alert success">Cms Updated Successfully! <a href="{SITE_URL}/admin/update">Return</a></div>
                </p>
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