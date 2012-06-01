<div class="left-box">
    <h2>Account Information</h2>
    <div class="left-box-content">
        <table border='0' cellspacing='0' cellpadding='1' >
        <tr>
            <td align='right' valign="top" width='25%'><b>Account Status:</b></td>
            <td align='left'  valign="top" width='25%'><b>{status}</b></td>
            <td align='right' valign="top" width='25%'><b>Vote Count:<b></td>
            <td align='left'  valign="top" width='25%'>{session.user.votes}</td>
        </tr>
        <tr>
            <td align='right' valign="top" width='25%'><b>Registration Date:</b></td>
            <td align='left'  valign="top" width='25%'>{joindate}</td>
            <td align='right' valign="top" width='25%'><b>Votepoints:<b></td>
            <td align='left'  valign="top" width='25%'>{session.user.vote_points}</td>
        </tr>
        <tr>
            <td align='right' valign="top" width='25%'><b>Registration IP:</b></td>									
            <td align='left'  valign="top" width='25%'>{session.user.registration_ip}</td>
            <td align='right' valign="top" width='25%'><b>Earned/Spent:<b></td>
            <td align='left'  valign="top" width='25%'>{session.user.vote_points_earned} / {session.user.vote_points_spent}</td>
        </tr>
        <tr>
            <td align='right' valign="top" width='25%'><b>Account Level:</b></td>
            <td align='left'  valign="top" width='25%'>{session.user.title}</td>
            <td align='right' valign="top" width='25%'><b>Total Donations:<b></td>
            <td align='left'  valign="top" width='25%'>${session.user.donations}</td>
        </tr>
        </table>
    </div>
</div>

<div class="left-box">
    <h2>Account Options</h2>
    <div class="left-box-content">
        <center>
            <a href="{SITE_URL}/account/update/password" class="button">Change Password</a>
            <a href="{SITE_URL}/account/update/email" class="button">Change Email</a>
			<a href="{SITE_URL}/account/invite_keys" class="button">Invitation Keys</a>
			<!--This is really just for aesthetics.-->
			<div style="height: 11px; width: 0px; background: transparent;"></div>
        </center>
    </div>
</div>