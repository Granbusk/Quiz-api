<?php

class User {
  private $name;
  private $email;
  private $password;

  public function  __construct($name=null, $email=null, $password=null) {
    parent::__construct();

    $this->name = $name;
    $this->email = $email;

    if (strlen(trim($password)) > 0) {
      $this->password = md5($password);
    }
  }

  public function signup() {
    if (strlen(trim($this->name)) == 0) {
      return false;
    }
    else if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $this->email)) {
      return false;
    }

    $stmt = DB::query("INSERT INTO user (`email`, `password`, `name`) VALUES (?, ?, ?)", $this->email, $this->password, $this->name);

    return DB::querySuccessful($stmt);
  }

  public function update($newPassword=null) {
    if (strlen(trim($this->name)) == 0) {
      return false;
    }
    else if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $this->email)) {
      return false;
    }

    $existingPassword = DB::fetchField(self::query("SELECT `password` FROM user WHERE `uid`=?", $_SESSION['uid']));

    if (isset($this->password)) {
      if($this->password != $existingPassword || !isset($newPassword)) {
        return false;
      }
      else {
        $this->password = md5($newPassword);
      }
    }
    else {
      $this->password = $existingPassword;
    }

    DB::query("UPDATE user SET `name`=?, `email`=?, `password`=? WHERE `uid`=?", $this->name, $this->email, $this->password, $_SESSION['uid']);

    return true;
  }

  public static function login($email, $password) {
    $passHash = md5($password);

    $stmt = DB::query("SELECT `uid` FROM user WHERE (`email`=? OR `name`=?) AND `password`=?", $email, $email, $passHash);

    $_SESSION['uid'] = DB::fetchField($stmt);

    return self::get($_SESSION['uid'], false);
  }

  public static function logout() {
    unset($_SESSION['uid']);
  }

  public static function get($uid=0, $removeEmail=true) {
    if ($uid == 0) {
      if (!isset($_SESSION['uid'])) {
        return null;
      }

      $uid = $_SESSION['uid'];
    }

    $stmt = DB::query("SELECT `uid`, `name`, `email` FROM user WHERE `uid`=?", $uid);

    $user = DB::fetchArray($stmt);

    $user['emailhash'] = md5($user['email']);

    if ($removeEmail) {
      unset($user['email']);
    }

    return $user;
  }
}