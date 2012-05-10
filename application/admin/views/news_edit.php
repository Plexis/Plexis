<div class="grid_12">
    <div class="block-border">
        <div class="block-header">
            <h1>News Form</h1><span></span>
        </div>
        <form id="validate-form" class="block-content form" action="{SITE_URL}/admin_ajax/news" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="{id}">
            
            <!-- Hidden Message -->
            <div id="js_news_message" style="display: none;"></div>
            
            <div class="_100">
                <p>
                    <label for="textfield">News Title</label>
                    <input id="textfield" name="title" class="required" type="text" value="{title}" />
                </p>
            </div>
            
            <div class="_100">
                <p>
                    <label for="textarea">News Body</label>
                    <textarea id="textarea" name="body" class="tinymce" rows="15" cols="40">{body}</textarea>
                </p>
            </div>
            
            <div class="clear"></div>
            <div class="block-actions">
                <ul class="actions-left">
                    <li><a class="button red" id="reset-validate-form" href="javascript:void(0);">Undo Changes</a></li>
                </ul>
                <ul class="actions-right">
                    <li><input type="submit" class="button" value="Update News Post"></li>
                </ul>
            </div>
        </form>
    </div>
</div>
<!-- Javascripts -->
<script type="text/javascript">
    var template_url = '{TEMPLATE_URL}';
</script>