<div class='comment-form'>
    <form method=post>
        <input type=hidden name="redirect" value="<?=$this->url->create("comment/index/$page")?>">
        <input type='hidden' name="page" value="<?=$page?>">
        <fieldset>
        <legend>Skriv en kommentar</legend>
        <p><label>Kommentarstext:<br/><textarea name='content'><?=$content?></textarea></label></p>
        <p><label>Namn:<br/><input type='text' name='name' value='<?=$name?>'/></label></p>
        <p><label>Hemsida:<br/><input type='text' name='web' value='<?=$web?>'/></label></p>
        <p><label>E-post:<br/><input type='text' name='mail' value='<?=$mail?>'/></label></p>
        <p class=buttons>
            <input type='submit' name='doCreate' value='Skicka' onClick="this.form.action = '<?=$this->url->create('comment/addWithPage')?>'"/>
            <input type='reset' value='Rensa'/>
            <input type='submit' name='doRemoveAll' value='Ta bort alla' onClick="this.form.action = '<?=$this->url->create('comment/remove-all-from-page')?>'"/>
        </p>
        <output><?=$output?></output>
        </fieldset>
    </form>
</div>
