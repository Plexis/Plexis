<!-- Button -->
<div>
    <a id="create" href="javascript:void(0);" class="button">Create New Post</a>
</div>

<!-- News List Table -->
<div class="block-border">
    <div class="block-header">
        <h1>News Posts</h1><span></span>
    </div>
    <div class="block-content">
        <div id="js_message" style="display: none;"></div>
        <table id="data-table" class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Posted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Hidden Create / Edit form -->
<div id="news-form" title="Create News Post" style="display: none;">
    <div id="js_news_message" style="display: none;"></div>
    <form id="news" class="form" action="{SITE_URL}/ajax/news" method="post">
        <input type="hidden" name="action" value="create">
        <div class="_100">
            <p>
                <label for="title">News Title</label>
                <input id="title" name="title" class="required" type="text" value="" />
            </p>
        
            <div>
                <label for="body">News Body</label>
                <textarea id="body" name="body" class="tinymce"></textarea>
            </div>

            <div>
                <input id="submit" type="submit" class="button" style="width: 150px; text-align: center; margin: 10px; float: right;" value="Submit">
            </div>
        </div>
    </form>
</div>

<!-- jForm for Ajax loading -->
<pcms::eval>
    <?php
        $this->append_metadata('');
        $this->append_metadata('<!-- Include jQuery Form css file -->');
        $this->append_metadata('<link type="text/javascript" src="'. SITE_URL .'/application/admin/js/mylibs/jquery.form.js"/>'); 
    ?>
</pcms::eval>
<script type="text/javascript">
    var template_url = '{TEMPLATE_URL}';
</script>