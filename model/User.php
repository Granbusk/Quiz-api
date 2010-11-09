<?php

class User {
  private $name;
  private $email;
  private $gravatar;
  private $gravatarUrl;
  private $password;

  public function  __construct($name=null, $email=null, $password=null, $gravatar=null) {
    $this->name = $name;
    $this->email = $email;
    $this->password = $password; // password is hashed by client
    $this->gravatar = $gravatar;
  }

  public function signup() {
    if (strlen(trim($this->name)) == 0) {
      return false;
    }
    else if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $this->email)) {
      return false;
    }
    else if ($this->password == '') {
      return false;
    }

    $stmt = DB::query("INSERT INTO `user` (`email`, `password`, `name`) VALUES (?, ?, ?)", $this->email, $this->password, $this->name);

    return DB::querySuccessful($stmt);
  }

  public function update($newPassword=null) {
    $error = array();
    
    if (strlen(trim($this->name)) == 0) {
      $error[] = 1;
    }
    else if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $this->email)) {      
      $error[] = 2;
    }
    
    $existingPassword = DB::fetchField(DB::query("SELECT `password` FROM `user` WHERE `uid`=?", $_SESSION['uid']));

    if ($this->password != '') {
      if($this->password != $existingPassword) {
        $error[] = 3;
      }
      else if ($newPassword == '') {
        $error[] = 4;
      }
      else {
        $this->password = $newPassword;
      }
    }
    else {
      $this->password = $existingPassword;
    }
    
    $nameInUse = DB::fetchField(DB::query("SELECT COUNT(*) FROM `user` WHERE `name`=? AND `uid`!=?", $this->name, $_SESSION['uid']));

    if ($nameInUse > 0) {
      $error[] = 5;
    }

    $emailInUse = DB::fetchField(DB::query("SELECT COUNT(*) FROM `user` WHERE `email`=? AND `uid`!=?", $this->email, $_SESSION['uid']));

    if ($emailInUse > 0) {
      $error[] = 6;
    }


    if (count($error) == 0) {
      DB::query("UPDATE `user` SET `name`=?, `email`=?, `password`=? WHERE `uid`=?", $this->name, $this->email, $this->password, $_SESSION['uid']);

      if (isset($this->gravatar)) {
        $this->gravatarUrl = $this->gravatar == 1 ? "http://www.gravatar.com/avatar/" . md5($this->email) : '';

        DB::query("UPDATE `user` SET `gravatar`=? WHERE `uid`=?", $this->gravatarUrl, $_SESSION['uid']);
      }

      return array(0);
    }

    return $error;
  }

  public static function login($email, $password) {
    $_SESSION['uid'] = DB::fetchField(DB::query("SELECT `uid` FROM `user` WHERE (`email`=? OR `name`=?) AND `password`=?", $email, $email, $password));
    
    return self::get($_SESSION['uid']);
  }

  public static function logout() {
    unset($_SESSION['uid']);
  }

  public static function get($uid=0, $removeEmail=true, $skipGravatar=false) {
    if ($uid == 0) {
      if (!isset($_SESSION['uid'])) {
        return null;
      }

      $uid = $_SESSION['uid'];
    }

    $user = DB::fetchArray(DB::query("SELECT `uid`, `name`, `email`, `gravatar` FROM `user` WHERE `uid`=?", $uid));

    if ($removeEmail) {
      unset($user['email']);
    }

    if ($skipGravatar) {
      unset($user['gravatar']);
    }

    return $user;
  }
}