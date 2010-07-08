<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '../../src/');

require_once 'config.php';
require_once 'MailRu.php';

$mr = new MailRu(APPLICATION_ID, APPLICATION_SECRET, $_REQUEST);

$canvas = $mr->getMobileCanvas($_GET['mobile_spec']);

echo <<<EOF
<html>
    <head>
        <title>mail.ru php library example 1</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    </head>
    <body>
EOF;
echo $canvas['header'];
echo <<<EOF
    <h1>current user info</h1>
    <ul>
EOF;

echo "<li>id: {$mr->getSession()->getViewer()->getId()}</li>";
echo "<li>first name: {$mr->getSession()->getViewer()->getFirstName()}</li>";
echo "<li>last name: {$mr->getSession()->getViewer()->getLastName()}</li>";
echo "<li>nick: {$mr->getSession()->getViewer()->getNick()}</li>";
echo "<li>link: <a href='{$mr->getSession()->getViewer()->getLink()}'>{$mr->getSession()->getViewer()->getLink()}</a></li>";
echo "<li>location: " . print_r($mr->getSession()->getViewer()->getLocation(), true) . "</li>";
echo "<li>is male: " . ($mr->getSession()->getViewer()->isMale() == true ? 'yes' : 'no') . "</li>";
echo "<li>photo: <a href='{$mr->getSession()->getViewer()->getPhoto()}'>{$mr->getSession()->getViewer()->getPhoto()}</a></li>";
echo "<li>photo big: <a href='{$mr->getSession()->getViewer()->getPhotoBig()}'>{$mr->getSession()->getViewer()->getPhotoBig()}</a></li>";
echo "<li>photo small: <a href='{$mr->getSession()->getViewer()->getPhotoSmall()}'>{$mr->getSession()->getViewer()->getPhotoSmall()}</a></li>";
echo "<li>birthday: {$mr->getSession()->getViewer()->getBirthday()}</li>";
echo "<li>referer: " . print_r($mr->getSession()->getViewer()->getReferer(), true) . "</li>";
echo '</ul>';
echo $canvas['footer'];
echo <<<EOF
    </body>
</html>
EOF;

