<div>
    <p>
        The admin of this site requires a registration or invite key to register an account
    </p>
</div>
<div>
    <form action='{SITE_URL}/account/register' method='POST'>
        <input type="hidden" name="action" value="key" />
        <div style="margin:1px;padding:6px 9px 6px 9px;text-align:center;">
            <label for="key">Registration Key: </label> <input type="text" id="key" size="50" name="key">
        </div>
        <div style="margin:1px;padding:6px 9px 0px 9px;text-align:center;">
            <input type="submit" size="16" class="button" style="font-size:12px;" value="Validate">
        </div>
    </form>
</div>