<?php

require_once 'MailRu/Session/LoggedIn.php';
require_once 'MailRu/User/IncompleteData.php';
require_once 'MailRu/User/Regular.php';
require_once 'MailRu/Transport/Curl.php';

require_once 'MailRu/Exception/IncorrectApplicationId.php';
require_once 'MailRu/Exception/IncorrectRequestParams.php';
require_once 'MailRu/Exception/InvalidSignature.php';
require_once 'MailRu/Exception/ServiceUnavailable.php';
require_once 'MailRu/Exception/AuthenticationFailed.php';
require_once 'MailRu/Exception/InsufficientPermissions.php';
require_once 'MailRu/Exception/RateLimitExceeded.php';
require_once 'MailRu/Exception/PaymentsDisabled.php';
require_once 'MailRu/Exception/ApplicationNotInstalled.php';
require_once 'MailRu/Exception/OtherError.php';

class MailRu {
    const LIBRARY_VERSION = '0.1';

    private $appId;
    private $secretKey;
    private $state;

    /**
     * @var MailRu_ISession
     */
    private $session;

    /**
     * @var MailRu_ITransport
     */
    private $transport;

    public static function getRequiredRequestParams() {
        return array('app_id', 'session_key', 'authentication_key', 'oid', 'vid', 'ext_perm');
    }

    public function __construct($appId, $secretKey, array $requestParams, MailRu_ITransport $transport = null) {
        $this->appId = $appId;
        $this->secretKey = $secretKey;
        $this->state = isset($requestParams['state']) ? $requestParams['state'] : null;

        if ($missingParams = array_diff(self::getRequiredRequestParams(), array_keys($requestParams))) {
            throw new MailRu_Exception_IncorrectRequestParams('One of the parameters specified is missing or invalid.', 100, $missingParams);
        }

        if ($appId != $requestParams['app_id']) {
            throw new MailRu_Exception_IncorrectApplicationId();
        }

        if ($requestParams['authentication_key'] != $this->calculateAuthenticationKey($this->appId, $requestParams['vid'], $this->secretKey)) {
            throw new MailRu_Exception_InvalidSignature();
        }

        $viewerUser = new MailRu_User_IncompleteData(array($this, 'getUser'), $requestParams['vid'], array(
            'permissions' => $requestParams['ext_perm'],
            'is_app_user' => !empty($requestParams['is_app_user']) ? $requestParams['is_app_user'] : false,
        ));
        $ownerUser = new MailRu_User_IncompleteData(array($this, 'getUser'), $requestParams['oid'], array('is_app_user' => true));
        $this->session = new MailRu_Session_LoggedIn($viewerUser, $ownerUser, $requestParams['session_key'], !empty($requestParams['window_id']) ? $requestParams['window_id'] : null);
        $this->transport = $transport ? $transport : new MailRu_Transport_Curl();
    }

    private function calculateAuthenticationKey($appId, $vid, $secretKey) {
        return md5($appId . '_' . $vid . '_' . $secretKey);
    }

    private function calculateRequestSignature($secretKey, array $requestParams) {
        ksort($requestParams);
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= "$key=$value";
        }
        return md5($params . $secretKey);
    }

    private function call($method, $params = array()) {
        if ($sk = $this->getSession()->getSessionKey()) {
            $params['session_key'] = $sk;
        }
        $params = array_merge($params, array(
            'format' => 'json',
            'app_id' => $this->appId,
            'secure' => '1',
            'method' => $method,
        ));
        $params['sig'] = $this->calculateRequestSignature($this->secretKey, $params);

        $result = $this->transport->get($params);

        $result = json_decode($result, true);
        if (is_array($result) && isset($result['error'])) {
            switch($result['error']['error_code']) {
                case '2': throw new MailRu_Exception_ServiceUnavailable($result['error']['error_msg'], $result['error']['error_code']); break;
                case '401': case '100': throw new MailRu_Exception_IncorrectRequestParams($result['error']['error_msg'], $result['error']['error_code']); break;
                case '102': throw new MailRu_Exception_AuthenticationFailed($result['error']['error_msg'], $result['error']['error_code']); break;
                case '104': throw new MailRu_Exception_InvalidSignature($result['error']['error_msg'], $result['error']['error_code']); break;
                case '200': throw new MailRu_Exception_InsufficientPermissions($result['error']['error_msg'], $result['error']['error_code']); break;
                case '201': throw new MailRu_Exception_RateLimitExceeded($result['error']['error_msg'], $result['error']['error_code']); break;
                case '301': throw new MailRu_Exception_PaymentsDisabled($result['error']['error_msg'], $result['error']['error_code']); break;
                case '5': case '105': throw new MailRu_Exception_ApplicationNotInstalled($result['error']['error_msg'], $result['error']['error_code']); break;
                default: throw new MailRu_Exception_OtherError($result['error']['error_msg'], $result['error']['error_code']);
            }
        }
        return $result;
    }

    public function getState() { return $this->state; }

    /**
     * @return MailRu_ISession
     */
    public function getSession() { return $this->session; }

    public function getMobileCanvas() {
        return $this->call('mobile.getCanvas');
    }

    /**
     * @return MailRu_IUser
     */
    public function getUser($id) {
        return reset($this->getUsers(array($id)));
    }

    /**
     * @return MailRu_IUser
     */
    public function getUsers(array $ids) {
        assert('count($ids) <= 200 && "You have exceeded limit for users in one users.getInfo call: 200"');
        $users = $this->call('users.getInfo', array('uids' => join(',', $ids)));
        return array_map(array('MailRu_User_Regular', 'fromRow'), $users);
    }

    /**
     * @return bool
     */
    public function isAppUser($id) {
        $result = $this->call('users.isAppUser', array('uid' => $id));
        return isset($result['isAppUser']) && $result['isAppUser'] == 1;
    }

    /**
     * @return bool
     */
    public function hasPermission($id, $permissionName) {
        $result = $this->call('users.hasAppPermission', array('uid' => $id, 'ext_perm' => $permissionName));
        return in_array($permissionName, array_keys(array_filter($result)));
    }

    /**
     * @return array of uids
     */
    public function getFriendsIds($id, $returnOnlyApplicationUsers = false) {
        if ($returnOnlyApplicationUsers) {
            return $this->call('friends.getAppUsers', array('uid' => $id));
        } else {
            return $this->call('friends.get', array('uid' => $id));
        }
    }

    /**
     * @return array of MailRu_IUser
     */
    public function getFriends($id, $returnOnlyApplicationUsers = false) {
        if ($returnOnlyApplicationUsers) {
            $users = $this->call('friends.getAppUsers', array('uid' => $id, 'ext' => 1));
        } else {
            $users = $this->call('friends.get', array('uid' => $id, 'ext' => 1));
        }
        return array_map(array('MailRu_User_Regular', 'fromRow'), $users);
    }

}
