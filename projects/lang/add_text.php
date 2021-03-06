<?php

require_once('config.php');
$session->requireLoggedIn();

require('design_head.php');

$selectedLang = 0;
$text = '';

if (!empty($_POST['text']) && !empty($_POST['lang']) && is_numeric($_POST['lang'])) {
    $selectedLang = $_POST['lang'];

    $text = $_POST['text'];

    analyzeText($selectedLang, $text);
}
?>

<h2>Add text</h2>

Here you can add longer chunks of text, and choose a language.<br/>
Each unique words, and their relations with other words within the sentences will be recorded.<br/>
Only useful for natural written language.<br/>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
    Language: <?php echo xhtmlSelectCategory(CATEGORY_LANGUAGE, 0, 'lang', !empty($_POST['lang']) ? $_POST['lang'] : '')?><br/>
    Text:<br/>
    <textarea name="text" cols="70" rows="20"></textarea><br/>
    <input type="submit" class="button" value="Add"/>
</form>

<?php

require('design_foot.php');
?>
