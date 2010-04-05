<?php

require_once 'MailRu/IUser.php';

class MailRu_User_IncompleteData implements MailRu_IUser {
    /**
     * @var MailRu_IUser
     */
    private $fullUser;
    private $id;
    private $knownParams;
    private $userInfoCallback;

    public function __construct($userInfoCallback, $id, $knownParams = array()) {
        $this->id = $id;
        $this->knownParams = $knownParams;
        $this->userInfoCallback = $userInfoCallback;
        if (isset($this->knownParams['sex']) && !isset($this->knownParams['is_male'])) {
            $this->knownParams['is_male'] = $this->knownParams['sex'] == MailRu_IUser::USER_SEX_MALE;
        }
    }

    public function getId() { return $this->id; }
    public function getFirstName() { return $this->getValue('first_name', __FUNCTION__); }
    public function getLastName() { return $this->getValue('last_name', __FUNCTION__); }
    public function getNick() { return $this->getValue('nick', __FUNCTION__); }
    public function getLink() { return $this->getValue('link', __FUNCTION__); }
    public function getLocation() { return $this->getValue('location', __FUNCTION__); }
    public function isMale() { return $this->getValue('is_male', __FUNCTION__); }
    public function getPhoto() { return $this->getValue('photo', __FUNCTION__); }
    public function getPhotoBig() { return $this->getValue('photo_big', __FUNCTION__); }
    public function getPhotoSmall() { return $this->getValue('photo_small', __FUNCTION__); }
    public function getBirthday() { return $this->getValue('birthday', __FUNCTION__); }
    public function getReferer() { return $this->getValue('referer', __FUNCTION__); }


    private function getValue($paramName, $funcName) {
        if ($this->fullUser) {
            return call_user_func(array($this->fullUser, $funcName));
        } elseif (array_key_exists($paramName, $this->knownParams)) {
            return $this->knownParams[$paramName];
        } else {
            $this->fullUser = call_user_func($this->userInfoCallback, $this->getId());
            assert('$this->fullUser instanceof MailRu_IUser && "callback result must implement IUser interface"');
            return call_user_func(array($this->fullUser, $funcName));
        }
    }

}
