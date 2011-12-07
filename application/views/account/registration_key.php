<div>
    <p>
        The admin of this site requires a registration or invite key to register an account
    </p>
</div>
<div>
    <form action='{SITE_URL}/account/register' method='POST'>
        <input type="hidden" name="action" value="key" />
        <div style="margin:1px;padding:6px 9px 6px 9px;text-align:center;">
            <b>Registration Key: </b> <input type="text" style="font-size:11px;" name="key">
        </div>
        <div style="margin:1px;padding:6px 9px 0px 9px;text-align:center;">
            <input type="submit" size="16" class="button" style="font-size:12px;" value="Validate">
        </div>
    </form>
</div>