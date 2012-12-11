<!-- Button -->
<div><a id="create" href="javascript:void(0);" class="button">Create New Post</a></div>

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
<div id="news-modal">
    <div id="js_news_message"></div>
    <form id="news-form" class="form" action="{SITE_URL}/admin_ajax/news" method="post">
        <input id="form-type" type="hidden" name="action" value="create">
        <input id="news-id" type="hidden" name="id" value="0">
        <div class="_100">
            <p>
                <label for="title">News Title</label>
                <input id="title" name="title" type="text" value="" />
            </p>
        
            <div>
                <label for="body">News Body</label>
                <textarea id="body" name="body" class="tinymce"></textarea>
            </div>

            <div>
                <br /><br />
                <center><input id="submit" type="submit" class="button" style="width: 150px; text-align: center; "></center>
            </div>
        </div>
    </form>
</div>