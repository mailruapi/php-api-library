<?php
require_once 'MailRu/IUser.php';

interface MailRu_ISession {
    public function getSessionKey();
    public function getWindowId();

    /**
     * @return MailRu_IUser
     */
    public function getViewer();

    /**
     * @return MailRu_IUser
     */
    public function getOwner();
}
