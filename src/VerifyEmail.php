<?php
  namespace hbattat;
  use \DOMDocument;
  use \DOMXpath;
  /**
   *  Verifies email address by attempting to connect and check with the mail server of that account
   *
   *  Author: huangjie 362223016@qq.com
   *
   *
   *  License: This code is released under the MIT Open Source License. (Feel free to do whatever)
   *
   *  Last update: Oct 11 2016
   *
   * @package VerifyEmail
   * @author  huangjie Battat <362223016@qq.com>
   * This is a test message for packagist
   */

  class VerifyEmail {
    public $email;
    public $thisrifier_email;
    public $port;
    private $mx;
    private $connect;
    private $errors;
    private $debug;
    private $debug_raw;

    private $_yahoo_signup_page_url = 'https://login.yahoo.com/account/create?specId=yidReg&lang=en-US&src=&done=https%3A%2F%2Fwww.yahoo.com&display=login';
    private $_yahoo_signup_ajax_url = 'https://login.yahoo.com/account/module/create?validateField=yid';
    private $_yahoo_domains = array('yahoo.com');
    private $_hotmail_signin_page_url = 'https://login.live.com/';
    private $_hotmail_username_check_url = 'https://login.live.com/GetCredentialType.srf?wa=wsignin1.0';
    private $_hotmail_domains = array('hotmail.com', 'live.com', 'outlook.com', 'msn.com');
    private $page_content;
    private $page_headers;
    private $recordLog;

    public $back_code = [
      'success' => ['status' => 1],
      'error' => ['status' => 2, 'msg' => 'Proxy Connect Failed'],
      'warning' => ['status' => 3, 'msg' => '邮件域名有误'],
      'sysError' => ['status' => 4, 'msg' => 'ACCESS DENY']
    ];
      public $cookies;
      public $proxyIp;
      public $proxyPort;
      public $proxyKey;

    public function __construct($email = null, $thisrifier_email = null, $port = 25){
      $this->debug = array();
      $this->debug_raw = array();
      if(!is_null($email) && !is_null($thisrifier_email)) {
        $this->debug[] = 'Initialized with Email: '.$email.', Verifier Email: '.$thisrifier_email.', Port: '.$port;
        $this->set_email($email);
        $this->set_verifier_email($thisrifier_email);
      }
      else {
        $this->debug[] = 'Initialized with no email or verifier email values';
      }
      $this->set_port($port);
        $this->cookies = '/4T/www/huangj/mailLog/yahooCookie.txt';
        $this->recordLog = '/4T/www/huangj/mailLog';
        $this->proxyKey = 'mail_yeah:proxy_ip_0';
        $this->proxyValue = '*************************';
    }

      public function set_proxy_info($proxyIp, $proxyPort) {
          $this->proxyIp = $proxyIp;
          $this->proxyPort = $proxyPort;
      }


    public function set_verifier_email($email) {
      $this->verifier_email = $email;
      $this->debug[] = 'Verifier Email was set to '.$email;
    }

    public function get_verifier_email() {
      return $this->verifier_email;
    }


    public function set_email($email) {
      $this->email = $email;
      $this->debug[] = 'Email was set to '.$email;
    }

    public function get_email() {
      return $this->email;
    }

    public function set_port($port) {
      $this->port = $port;
      $this->debug[] = 'Port was set to '.$port;
    }

    public function get_port() {
      return $this->port;
    }

    public function get_errors(){
      return array('errors' => $this->errors);
    }

    public function get_debug($raw = false) {
      if($raw) {
        return $this->debug_raw;
      }
      else {
        return $this->debug;
      }
    }

    public function verify() {
      $this->debug[] = 'Verify function was called.';

      $result = [];

      //check if this is a yahoo email
      $domain = $this->get_domain($this->email);
      if(in_array(strtolower($domain), $this->_yahoo_domains)) {
//        $result = $this->validate_yahoo();
        $result = $this->validate_yahoo();
      }
      else if(in_array(strtolower($domain), $this->_hotmail_domains)){
        $result = $this->validate_hotmail();
      }
      //otherwise check the normal way
//      else {
//        //find mx
//        $this->debug[] = 'Finding MX record...';
//        $this->find_mx();
//
//        if(!$this->mx) {
//          $this->debug[] = 'No MX record was found.';
//          $this->add_error('100', 'No suitable MX records found.');
//          return $is_valid;
//        }
//        else {
//          $this->debug[] = 'Found MX: '.$this->mx;
//        }
//
//
//        $this->debug[] = 'Connecting to the server...';
//        $this->connect_mx();
//
//        if(!$this->connect) {
//          $this->debug[] = 'Connection to server failed.';
//          $this->add_error('110', 'Could not connect to the server.');
//          return $is_valid;
//        }
//        else {
//          $this->debug[] = 'Connection to server was successful.';
//        }
//
//
//        $this->debug[] = 'Starting veriffication...';
//        if(preg_match("/^220/i", $out = fgets($this->connect))){
//          $this->debug[] = 'Got a 220 response. Sending HELO...';
//          fputs ($this->connect , "HELO ".$this->get_domain($this->verifier_email)."\r\n");
//          $out = fgets ($this->connect);
//          $this->debug_raw['helo'] = $out;
//          $this->debug[] = 'Response: '.$out;
//
//          $this->debug[] = 'Sending MAIL FROM...';
//          fputs ($this->connect , "MAIL FROM: <".$this->verifier_email.">\r\n");
//          $from = fgets ($this->connect);
//          $this->debug_raw['mail_from'] = $from;
//          $this->debug[] = 'Response: '.$from;
//
//          $this->debug[] = 'Sending RCPT TO...';
//          fputs ($this->connect , "RCPT TO: <".$this->email.">\r\n");
//          $to = fgets ($this->connect);
//          $this->debug_raw['rcpt_to'] = $to;
//          $this->debug[] = 'Response: '.$to;
//
//          $this->debug[] = 'Sending QUIT...';
//          $quit = fputs ($this->connect , "QUIT");
//          $this->debug_raw['quit'] = $quit;
//          fclose($this->connect);
//
//          $this->debug[] = 'Looking for 250 response...';
//          if(!preg_match("/^250/i", $from) || !preg_match("/^250/i", $to)){
//            $this->debug[] = 'Not found! Email is invalid.';
//            $is_valid = false;
//          }
//          else{
//            $this->debug[] = 'Found! Email is valid.';
//            $is_valid = true;
//          }
//        }
//        else {
//          $this->debug[] = 'Encountered an unknown response code.';
//        }
//      }

      return $result;
    }

      /**
       * 验证邮箱注册情况
       * @param $proxy
       * @param $mail
       * @return array|bool|mixed
       */
      public function _verifyEmail ($mail) {
          $proxy = $this->getProxyIp($this->proxyKey);
          $this->set_email($mail);
          $this->set_proxy_info($proxy['proxyIp'], $proxy['proxyPort']);
          $result = $this->verify();

          if ($result['status'] == 1) {
              ///注册验证成功
              var_dump("pass: {$mail}");
//            $strJson = '{"errors":[{"name":"firstName","error":"FIELD_EMPTY"},{"name":"lastName","error":"FIELD_EMPTY"},{"name":"yid","error":"IDENTIFIER_EXISTS"},{"name":"password","error":"FIELD_EMPTY"},{"name":"birthDate","error":"INVALID_BIRTHDATE"},{"name":"phone","error":"FIELD_EMPTY"}]}';
              $strArr = json_decode($result['msg'], true);
              foreach ($strArr['errors'] as $key => $value) {
                  if ( $value['name'] == "yid" && $value['error'] == "IDENTIFIER_EXISTS" ){
                      var_dump('这个邮件已经存在');
                      return false;
                  }
              }
              return true;
          } elseif($result['status'] == 2 || $result['status'] == 4) {
              //代理ip问题，重调一次
              $this->proxy->setIpRedis($this->proxyKey, $this->proxyValue);
              var_dump("发送邮件信息->{$mail}");
              $result = $this->_verifyEmail($mail);
              return $result;
          } elseif ($result['status'] == 3) {
              var_dump("fail: {$mail}");
              return false;
          } else {
              var_dump("fail: {$mail}");
              return false;
          }
      }

      /**
       * 获取代理ip
       * @return mixed
       */
      public function getProxyIp($proxyIpKey) {
          $proxy = Redis::hgetAll($proxyIpKey);

          if (isset($proxy['expire_time']) && strtotime($proxy['expire_time']) > time()) {
              return $proxy;
          } else {
              sleep(1);
              $this->proxy->setIpRedis($proxyIpKey, $this->proxyValue);
              $proxy = $this->getProxyIp($proxyIpKey);
              return $proxy;
          }
      }



    private function get_domain($email) {
      $email_arr = explode('@', $email);
      $domain = array_slice($email_arr, -1);
      return $domain[0];
    }
    private function find_mx() {
      $domain = $this->get_domain($this->email);
      $mx_ip = false;
      // Trim [ and ] from beginning and end of domain string, respectively
      $domain = ltrim($domain, '[');
      $domain = rtrim($domain, ']');

      if( 'IPv6:' == substr($domain, 0, strlen('IPv6:')) ) {
        $domain = substr($domain, strlen('IPv6') + 1);
      }

      $mxhosts = array();
      if( filter_var($domain, FILTER_VALIDATE_IP) ) {
        $mx_ip = $domain;
      }
      else {
        getmxrr($domain, $mxhosts, $mxweight);
      }

      if(!empty($mxhosts) ) {
        $mx_ip = $mxhosts[array_search(min($mxweight), $mxweight)];
      }
      else {
        if( filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
          $record_a = dns_get_record($domain, DNS_A);
        }
        elseif( filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
          $record_a = dns_get_record($domain, DNS_AAAA);
        }

        if( !empty($record_a) ) {
          $mx_ip = $record_a[0]['ip'];
        }

      }

      $this->mx = $mx_ip;
    }


    private function connect_mx() {
      //connect
      $this->connect = @fsockopen($this->mx, $this->port);
    }

    private function add_error($code, $msg) {
      $this->errors[] = array('code' => $code, 'message' => $msg);
    }

    private function clear_errors() {
      $this->errors = array();
    }

    private function validate_yahoo() {
      $this->debug[] = 'Validating a yahoo email address...';
      $this->debug[] = 'Getting the sign up page content...';
        $pageRes = [];
      $pageRes = $this->fetch_page('yahoo');
        ///代理失败返回
        if ($pageRes['status'] != 1) {
            return $pageRes;
        }

//      $cookies = $this->get_cookies();
      $fields = $this->get_fields();

      $this->debug[] = 'Adding the email to fields...';
      $fields['yid'] = str_replace('@yahoo.com', '', strtolower($this->email));

      $this->debug[] = 'Ready to submit the POST request to validate the email.';

      $response = $this->request_validation('yahoo', $fields);

      return $response;
//      $this->debug[] = 'Parsing the response...';
//      $response_errors = json_decode($response, true)['errors'];
//
//      $this->debug[] = 'Searching errors for exisiting username error...';
//      foreach($response_errors as $err){
//        if($err['name'] == 'yid' && $err['error'] == 'IDENTIFIER_EXISTS'){
//          $this->debug[] = 'Found an error about exisiting email.';
//          return true;
//        }
//      }
//      return false;
    }

    private function validate_hotmail() {
      $this->debug[] = 'Validating a hotmail email address...';
      $this->debug[] = 'Getting the sign up page content...';
        $pageRes = [];
        $pageRes = $this->fetch_page('hotmail');
        ///代理失败返回
        if ($pageRes['status'] != 1) {
            return $pageRes;
        }

        $fields['yid'] = str_replace('@hotmail.com', '', strtolower($this->email));

      $this->debug[] = 'Ready to submit the POST request to validate the email.';
      $response = $this->request_validation('hotmail', $fields);

        return $response;
    }

    private function fetch_page($service, $cookies = ''){
      $page = [];
      if($service == 'yahoo'){
          $page = [];
          $page = $this->proxy_curl($this->_yahoo_signup_page_url, $this->proxyIp, $this->proxyPort, $service);
      }
      else if($service == 'hotmail'){
          $page = [];
          $page = $this->proxy_curl($this->_hotmail_signin_page_url, $this->proxyIp, $this->proxyPort, $service);
      }

      return $page;
    }

    private function get_cookies(){
      $this->debug[] = 'Attempting to get the cookies from the sign up page...';
      if($this->page_content !== false){
        $this->debug[] = 'Extracting cookies from headers...';
        $cookies = array();
        foreach ($this->page_headers as $hdr) {
          if (preg_match('/^Set-Cookie:\s*(.*?;).*?$/i', $hdr, $matches)) {
            $cookies[] = $matches[1];
          }
        }

        if(count($cookies) > 0){
          $this->debug[] = 'Cookies found: '.implode(' ', $cookies);
          return $cookies;
        }
        else{
          $this->debug[] = 'Could not find any cookies.';
        }
      }
      return false;
    }

    private function get_fields(){
      $dom = new DOMDocument();
      $fields = array();
      if(@$dom->loadHTML($this->page_content)){
        $this->debug[] = 'Parsing the page for input fields...';
        $xp = new DOMXpath($dom);
        $nodes = $xp->query('//input');
        foreach($nodes as $node){
          $fields[$node->getAttribute('name')] = $node->getAttribute('value');
        }

        $this->debug[] = 'Extracted fields.';
      }
      else{
        $this->debug[] = 'Something is worng with the page HTML.';
        $this->add_error('210', 'Could not load the dom HTML.');
      }
      return $fields;
    }

    private function request_validation($service, $fields){
      if($service == 'yahoo'){
          $result = $this->proxy_curl($this->_yahoo_signup_ajax_url, $this->proxyIp, $this->proxyPort, $service, $fields, $this->cookies);
      }
      else if($service == 'hotmail'){
          $result = $this->proxy_curl($this->_hotmail_username_check_url, $this->proxyIp, $this->proxyPort, $service, $fields, $this->cookies);
      }
      return $result;
    }

    private function prep_hotmail_fields($cookies){
      $fields = array();
      foreach($cookies as $cookie){
        list($key, $val) = explode('=', $cookie, 2);
        if($key == 'uaid'){
          $fields['uaid'] = $val;
          break;
        }
      }
      $fields['username'] = strtolower($this->email);
      return $fields;
    }

      /**
       * curl获取163注册端口
       * @param $url
       * @param $proxy
       * @param $proxyPort
       * @param string $data
       * @param string $cookie
       * @return mixed
       */
      private function proxy_curl($url, $proxy, $proxyPort, $service, $data='', $cookie = ''){
          $headers = array();
          $headers[] = 'Origin: https://login.yahoo.com';
          $headers[] = 'X-Requested-With: XMLHttpRequest';
          $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36';
          $headers[] = 'content-type: application/x-www-form-urlencoded; charset=UTF-8';
          $headers[] = 'Accept: */*';
          $headers[] = 'Referer: https://login.yahoo.com/account/create?specId=yidReg&lang=en-US&src=&done=https%3A%2F%2Fwww.yahoo.com&display=login';
          $headers[] = 'Accept-Encoding: gzip, deflate, br';
          $headers[] = 'Accept-Language: en-US,en;q=0.8,ar;q=0.6';
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_PROXY, $proxy); //代理ip
          curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort); //代理端口
//        if ( $cookie ) {
          curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); //管道
//        }
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          if ($cookie) {
              curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
              curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookies); //cookie
          } else {
              curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookies);
          }
          curl_setopt($ch, CURLOPT_TIMEOUT, 60);
          if ($data) {
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
          }
          $file = $this->recordLog. "/{$service}.log";
          $op = fopen($file, "a");
          curl_setopt($ch, CURLOPT_VERBOSE, true); // Uncomment to see the transaction
          curl_setopt($ch, CURLOPT_STDERR, $op);
          fwrite($op,"##{$proxy}###\n");
          $res = curl_exec($ch);
          if ( empty($cookie) ) {
              $this->page_content = $res;
//              $this->page_headers = $http_response_header;
              $this->page_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
          }
          curl_close($ch);
          fclose($op);

          $file_content_temp = file_get_contents($file);
          $file_content = explode("##{$proxy}###", $file_content_temp);
          $array = explode(PHP_EOL, $file_content[count($file_content)-1]);

          foreach ($array as $k => $v) {
              if (preg_match('/Recv failure: Connection reset by peer/', $v)) { ///proxy connect
                  return $this->back_code['error'];
              }
              else if (preg_match("/connect to {$proxy} port {$proxyPort} failed/", $v)) {///proxy failed
                  return $this->back_code['error'];
              }
              else if (preg_match("/Connection timed out after/", $v)) {///Connection timed out
                  return $this->back_code['error'];
              }
          }

          if (isset($res['code'])) {
              ///ip被封
              if ($res['code'] == 400) {
                  return $this->back_code['sysError'];
              }
          }

          $this->back_code['success']['msg'] = $res;
          return $this->back_code['success'];
      }

      /**
       * 获取代理ip
       * @return mixed
       */
      public function getProxyIp($proxyIpKey) {
          $proxy = $this->redis->hgetAll($proxyIpKey);

          if (isset($proxy['expire_time']) && strtotime($proxy['expire_time']) > time()) {
              return $proxy;
          } else {
              sleep(1);
              $this->proxy->setIpRedis($proxyIpKey, $this->proxyValue);
              $proxy = $this->getProxyIp($proxyIpKey);
              return $proxy;
          }
      }

  }
