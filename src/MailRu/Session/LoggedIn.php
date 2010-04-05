<?php

require_once 'MailRu/ISession.php';
require_once 'MailRu/IUser.php';

class MailRu_Session_LoggedIn implements MailRu_ISession {

    /**
     * @var MailRu_IUser
     */
    private $viewerUser;

    /**
     * @var MailRu_IUser
     */
    private $ownerUser;

    private $sessionKey;
    private $windowId;

    public function __construct(MailRu_IUser $viewerUser, MailRu_IUser $ownerUser, $sessionKey, $windowId) {
        $this->viewerUser = $viewerUser;
        $this->ownerUser = $ownerUser;
        $this->sessionKey = $sessionKey;
        $this->windowId = $windowId;
    }

    public function getSessionKey() {
        return $this->sessionKey;
    }

    public function getWindowId() {
        return $this->windowId;
    }

    /**
     * @return MailRu_IUser
     */
    public function getViewer() {
        return $this->viewerUser;
    }

    /**
     * @return MailRu_IUser
     */
    public function getOwner() {
        return $this->ownerUser;
    }
}
