<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '../../src/');

define('APPLICATION_ID', '424183');
define('APPLICATION_SECRET', '4a8ef33edfa18a0de22bf9d917ddbcb5');

require_once 'MailRu.php';

$mr = new MailRu(APPLICATION_ID, APPLICATION_SECRET, $_REQUEST, new MailRu_Transport_Curl('http://my.alei29.netbridge.ru/cgi-bin/app/newapi'));

echo <<<EOF
<html>
    <head>
        <title>mail.ru php library example 1</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    </head>
    <body>
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

echo <<<EOF
    </ul>
    </body>
</html>
EOF;