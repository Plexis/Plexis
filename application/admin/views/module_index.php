<div class="grid_12">

    <!-- Module Table -->
    <div class="block-border">
        <div class="block-header">
            <h1>Modules</h1><span></span>
        </div>
        <div class="block-content">
            <div id="js_message" style="display: none;"></div>
            <table id="module-table" class="table">
                <thead>
                    <tr>
                        <th>Module Name</th>
                        <th>Uri</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden Install form -->
<div id="install-form" title="Install Module" style="display: none;">
    <div id="js_install_message" style="display: none;"></div>
    <form id="install" class="form" action="{SITE_URL}/ajax/modules" method="post" style="">
        <input type="hidden" name="action" value="install">
        <div class="_100">
            <p>
                <div>
                    <label for="module">Module Name</label>
                    <input id="module" name="module" class="required" readonly="readonly" type="text" value="name" />
                </div>
            </p>
        
            <p>
                <div>
                    <label for="uri">Uri</label>
                    <input id="uri" name="uri" class="required" type="text" value="" />
                </div>
            </p>
            
            <p>
                <div>
                    <label for="function">function</label>
                    <input id="function" name="function" class="required" type="text" value="" />
                </div>
            </p>

            <div>
                <input id="submit" type="submit" class="button" style="width: 150px; text-align: center; margin: 10px; float: right;" value="Submit">
            </div>
        </div>
    </form>
</div>


<script type="text/javascript">
    $().ready(function() {
        // Define our URL here
        var url = '{SITE_URL}';
        
        /*
         * DataTables
         */
        var modtable = $('#module-table').dataTable({
            "bServerSide": true,
            "bSortClasses": false,
            "sAjaxSource": url + "/ajax/modules",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                aoData.push( { "name": "action", "value": "getlist" } );
                $.ajax( {
                    "dataType": 'json', 
                    "type": "POST", 
                    "url": sSource, 
                    "data": aoData, 
                    "success": fnCallback
                } );
            }
        });
        
        // ============================================
        // Install Module
        $("#module-table").delegate('.install', 'click', function(){
            var name = $(this).attr('name');
            
            // Show form, and hide any previous messages
            $('#install-form').dialog({ modal: true, height: 300, width: 500 });
            $('#install').removeAttr( "style" );
            $('#js_install_message').attr('style', 'display: none;');
            $('input[name=module]').val( name );
            $('input[name=function]').val( 'index' );
            $('input[name=uri]').val( 'seg1/seg2' );
            
        });
        
        // ============================================
        // Uninstall Module
        $("#module-table").delegate('.un-install', 'click', function(){
            var name = $(this).attr('name');
            
            if( confirm('Are you sure you want to uninstall Module "' + name + '"?') ){
                // Get our post
                $.post("{SITE_URL}/ajax/modules", { action : 'un-install', name : name },
                    function(response){
                        // Parse the JSON response
                        var result = jQuery.parseJSON(response);
                        if (result.success == true)
                        {
                            // Display our Success message, and ReDraw the table so we imediatly see our action
                            $('#js_message').attr('class', 'alert success').html(result.message).slideDown(300).delay(3000).slideUp(600);
                            modtable.fnDraw();
                        }
                        else
                        {
                            $('#js_message').attr('class', 'alert ' + result.type).html(result.message).slideDown(300).delay(3000).slideUp(600);
                        }
                    }
                );
            }
        });
        
        // ===============================================
        // bind the Install form using 'ajaxForm' 
        $('#install').ajaxForm({
            beforeSubmit: function (arr, data, options){
                $('#install').attr('style', 'display: none');
                $('#js_install_message').attr('class', 'alert loading').html('Submitting Form...').slideDown(300);
                $( "#install-form" ).dialog( "option", "height", 150 );
            },
            success: install_result,
            clearForm: true,
            timeout: 5000 
        });

        // Callback function for the News ajaxForm 
        function install_result(response, statusText, xhr, $form)  {
            // Parse the JSON response
            var result = jQuery.parseJSON(response);
            if (result.success == true){
                // Display our Success message, and ReDraw the table so we imediatly see our action
                $('#js_install_message').attr('class', 'alert success').html(result.message);
                modtable.fnDraw();
            }else{
                $('#js_install_message').attr('class', 'alert ' + result.type).html(result.message);
            }
        }
    });
</script>