<?php

require_once 'MailRu/IUser.php';

class MailRu_User_Regular implements MailRu_IUser {
    private $id;
    private $firstName;
    private $lastName;
    private $nick;
    private $link;
    private $location;
    private $sex;
    private $pic;
    private $birthday;
    private $referer;

    public function __construct($id, $firstName, $lastName, $nick, $link, $location, $sex, $pic, $picBig, $picSmall, $birthday, $referer) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->nick = $nick;
        $this->link = $link;
        $this->location = $location;
        $this->sex = $sex;
        $this->pic = $pic;
        $this->picBig = $picBig;
        $this->picSmall = $picSmall;
        $this->birthday = $birthday;
        $this->referer = $referer;
    }

    public function getId() { return $this->id; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getNick() { return $this->nick; }
    public function getLink() { return $this->link; }
    public function getLocation() { return $this->location; }
    public function isMale() { return $this->sex == MailRu_IUser::USER_SEX_MALE; }
    public function getPhoto() { return $this->pic; }
    public function getPhotoBig() { return $this->picBig; }
    public function getPhotoSmall() { return $this->picSmall; }
    public function getBirthday() { return $this->birthday; }
    public function getReferer() { return $this->referer; }

    public static function fromRow($user) {
        return new self(
            $user['uid'],
            $user['first_name'],
            $user['last_name'],
            $user['nick'],
            $user['link'],
            $user['location'],
            $user['sex'],
            $user['pic'],
            $user['pic_big'],
            $user['pic_small'],
            $user['birthday'],
            $user['referer_type'] ? array($user['referer_type'], $user['referer_id']) : null
        );
    }
}
