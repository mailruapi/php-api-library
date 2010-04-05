<?php

interface MailRu_IUser {
    const USER_SEX_MALE = 0;
    const USER_SEX_FEMALE = 1;

    public function getId();
    public function getFirstName();
    public function getLastName();
    public function getNick();
    public function getLink();
    public function getLocation();
    public function isMale();
    public function getPhoto();
    public function getPhotoBig();
    public function getPhotoSmall();
    public function getBirthday();
    public function getReferer();
}
