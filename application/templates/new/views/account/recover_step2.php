<div class="left-box">
    <h2>Account Recovery Step 2</h2>
    <div class="left-box-content">
        <p>
            In order to complete the account recovery process, you must answer to the secret question you selected
            when you registered your account with us. If you were never prompted to enter a secret question and answer
            when you registered with us, Please contact an administrator.
        </p>
        <form method="post" action="{SITE_URL}/account/recover" id="recover-form">
            <input type="hidden" name="action" value="recover" />
            <input type="hidden" name="step" value="2" />
            <input type="hidden" name="username" value="{username}" />
            <input type="hidden" name="question" value="{question}" />
            
            <fieldset>
                <div class="div-center">
                    <center>{question}</center><br />
                    
                    <label for="answer">Answer:</label> 
                    <input type="text" name="answer" id="answer" value="" size="30" tabindex="2" />
                    
                    <center><input type="submit" name="submit" value="Submit" class="button" tabindex="3" /></center>
                </div>
            </fieldset>
        </form>
    </div>
</div>