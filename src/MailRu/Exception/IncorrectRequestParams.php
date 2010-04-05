<?php
require_once 'MailRu/Exception.php';

class MailRu_Exception_IncorrectRequestParams extends MailRu_Exception {
  public function __construct($message, $code, $missingParams = array()) {
    parent::__construct($message, $code);
    $this->missingParams = $missingParams;
  }

  public function getMissingParams() {
    return $this->missingParams;
  }

}
