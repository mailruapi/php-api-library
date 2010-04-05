<?php
require_once 'PHPUnit/Framework.php';
require_once 'MailRu.php';

class MockTransport implements MailRu_ITransport {
    private $lastParams;
    private $result;

    public function __construct($resultToReturn) {
        $this->result = $resultToReturn;
    }

    public function get($params) {
        $this->lastParams = $params;
        return $this->result;
    }

    public function getLastUsedParams() { return $this->lastParams; }
}

class MailruTest extends PHPUnit_Framework_TestCase
{
  const APP_ID = '31337';
  const SECRET_KEY = '__secret_key__';

  private function getCorrectRequestParams($permissions = null, $vid = null, $oid = null) {
    $params = array(
      'app_id' => self::APP_ID,
      'session_key' => '__unit_test__',
      'oid' => $oid ? $oid : '123',
      'vid' => $vid ? $vid : '234',
      'is_app_user' => '1',
      'state' => '__state__',
      'ext_perm' => $permissions ? join(',', $permissions) : 'stream,notifications',
      'window_id' => '__window_id__',
    );
    $params['authentication_key'] = md5($params['app_id'] . '_' . $params['vid'] . '_' . self::SECRET_KEY);
    return $params;
  }

  private function getTestUserJson() {
    return '{"link":"http://my.alei29.netbridge.ru/corp/bitman/","location":{"country":{"name":"Россия","id":"24"},"city":{"name":"Москва","id":"25"},"region":{"name":"Москва","id":"999999"}},"uid":"1324730981306483817","nick":"Дима Бэтман","sex":0,"pic_small":"http://avt.appsmail.ru/corp/bitman/_avatarsmall","pic_big":"http://avt.appsmail.ru/corp/bitman/_avatarbig","pic":"http://avt.appsmail.ru/corp/bitman/_avatar","last_name":"Бэтман","referer_id":"","birthday":"24.12.1985","referer_type":"","first_name":"Дима"}';
  }

  private function getTestUserObject() {
    $location = array ( 'country' => array ( 'name' => 'Россия', 'id' => '24', ), 'city' => array ( 'name' => 'Москва', 'id' => '25', ), 'region' => array ( 'name' => 'Москва', 'id' => '999999', ), );
    return new MailRu_User_Regular(
        '1324730981306483817',
        'Дима',
        'Бэтман',
        'Дима Бэтман',
        'http://my.alei29.netbridge.ru/corp/bitman/',
        $location,
        0,
        'http://avt.appsmail.ru/corp/bitman/_avatar',
        'http://avt.appsmail.ru/corp/bitman/_avatarbig',
        'http://avt.appsmail.ru/corp/bitman/_avatarsmall',
        '24.12.1985',
        null
    );
  }

  /**
   * @expectedException MailRu_Exception_IncorrectRequestParams
   */
  public function testRequestParamsCanNotBeEmpty()
  {
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, array());
  }

  /**
   * @dataProvider requiredRequestParamsProvider
   * @expectedException MailRu_Exception_IncorrectRequestParams
   */
  public function testRequestParamsMustIncludeRequiredParams($paramToLeaveOut)
  {
    $rp = $this->getCorrectRequestParams();
    unset($rp[$paramToLeaveOut]);
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $rp);
  }

  public function requiredRequestParamsProvider()
  {
    $requiredParams = MailRu::getRequiredRequestParams();
    $result = array();
    foreach ($requiredParams as $param) {
      $result[] = array($param);
    }
    return $result;
  }

  /**
   * @expectedException MailRu_Exception_IncorrectApplicationId
   */
  public function testApplicationIdsMustMatch()
  {
    $mailru = new MailRu(self::APP_ID . '123', self::SECRET_KEY, $this->getCorrectRequestParams());
  }

  /**
   * @expectedException MailRu_Exception_InvalidSignature
   */
  public function testRequestSignatureMustBeValid()
  {
    $rp = $this->getCorrectRequestParams();
    $rp['authentication_key'] = '__wrong__';
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $rp);
  }

  public function testObjectCanBeCreated()
  {
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams());
    $this->assertTrue($mailru instanceof MailRu);
  }

  public function testCanGetCurrentSession()
  {
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams());
    $this->assertTrue($mailru->getSession() instanceof MailRu_Session_LoggedIn);
  }

  public function testSessionKeyIsAlywaysPassedToApiIfKnown() {
    $transport = new MockTransport('[1,2,3]');
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(), $transport);
    $mailru->getFriendsIds('1');
    $params = $transport->getLastUsedParams();
    $this->assertEquals($mailru->getSession()->getSessionKey(), $params['session_key']);
  }

  public function testSignatureIsCalculatedCorrectly() {
    $transport = new MockTransport('[1,2,3]');
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(), $transport);
    $mailru->getFriendsIds('1');
    $params = $transport->getLastUsedParams();
    $this->assertEquals($params['sig'], 'b97d6d06acb98f6b66dfb297d773c352');
  }

  public function testCanGetFriendsIds() {
    $transport = new MockTransport('[1,2,3]');
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(), $transport);
    $this->assertEquals(array('1','2','3'), $mailru->getFriendsIds('1'));
  }

  public function testCanGetFriends() {
    $transport = new MockTransport('[' . $this->getTestUserJson() . ']');
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(), $transport);
    $this->assertEquals(array($this->getTestUserObject()), $mailru->getFriends('1'));
  }

  public function testCanGetUser() {
    $transport = new MockTransport('[' . $this->getTestUserJson() . ']');
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(), $transport);
    $this->assertEquals($this->getTestUserObject(), $mailru->getUser('1'));
  }

  public function testCanGetMultipleUsers() {
    $transport = new MockTransport('[' . $this->getTestUserJson() . ',' . $this->getTestUserJson() . ']');
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(), $transport);
    $this->assertEquals(array($this->getTestUserObject(), $this->getTestUserObject()), $mailru->getUsers(array('1','2')));
  }

  public function testSessionKeyIsCorrect() {
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams());
    $this->assertEquals('__unit_test__', $mailru->getSession()->getSessionKey());
  }

  public function testWindowIdIsCorrect() {
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams());
    $this->assertEquals('__window_id__', $mailru->getSession()->getWindowId());
  }

  public function testViewerIsCorrect() {
    $transport = new MockTransport('[' . $this->getTestUserJson() . ']');
    $reference = $this->getTestUserObject();
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(null, $reference->getId()), $transport);
    $this->assertUsersEqual($reference, $mailru->getSession()->getViewer());
  }

  public function testOwnerIsCorrect() {
    $transport = new MockTransport('[' . $this->getTestUserJson() . ']');
    $reference = $this->getTestUserObject();
    $mailru = new MailRu(self::APP_ID, self::SECRET_KEY, $this->getCorrectRequestParams(null, null, $reference->getId()), $transport);
    $this->assertUsersEqual($reference, $mailru->getSession()->getOwner());
  }

  private function assertUsersEqual($reference, $user) {
    $rc = new ReflectionClass('MailRu_IUser');
    foreach ($rc->getMethods() as $method) {
        $method = $method->getName();
        if (strpos($method, 'get') === 0) {
            $this->assertEquals(call_user_func(array($reference, $method)), call_user_func(array($user, $method)));
        }
    }
  }

}
