<?php
class Group {

  public static function create($name, $password) {
    Security::requireLoggedIn();

    if (strlen(trim($name)) == 0) {
      return false;
    }

    DB::query("INSERT INTO `group` (`name`, `password`) VALUES (?, ?)", $name, $password);

    $gid = DB::getInsertId();
    
    if ($gid > 0) {
      DB::query("INSERT INTO `user_group` (`uid`, `gid`, `moderator`, `administrator`) VALUES (?, ?, ?, ?)", $_SESSION['uid'], $gid, 1, 1);
    }

    return $gid;
  }

  public static function getUsers($gid) {
    return DB::fetchAll(DB::query("SELECT `u`.`uid`, `u`.`name` FROM `user` `u`, `user_group` `ug` WHERE `ug`.`gid`=? AND `ug`.`uid`=`u`.`uid` AND `ug`.`left`=?", $gid, 0));
  }

  public static function getById($gid) {
    return DB::fetchArray(DB::query("SELECT `g`.`name`, (SELECT COUNT(*) FROM `user_group` `ug` WHERE `ug`.`gid`=`g`.`gid` AND `ug`.`uid`=? AND (`ug`.`moderator`=?)) AS `moderator` FROM `group` `g` WHERE `g`.`gid`=?", $_SESSION['uid'], 1, $gid));
  }

  public static function getMine() {
    Security::requireLoggedIn();
    
    return DB::fetchAll(DB::query("SELECT `g`.`gid`, `g`.`name` FROM `group` `g`, `user_group` `ug` WHERE `ug`.`uid`=? AND `ug`.`gid`=`g`.`gid`", $_SESSION['uid']));
  }

  public static function userCanCreateQuestion($gid) {
    $isMod = DB::fetchField(DB::query("SELECT COUNT(*) FROM `user_group` WHERE `uid`=? AND `gid`=? AND (`moderator`=? OR `administrator`=?)", $_SESSION['uid'], $gid, 1, 1));
    
    return $isMod == 1;
  }

  public static function join($gid, $password) {
    Security::requireLoggedIn();

    $groupPassword = DB::fetchField(DB::query("SELECT `password` FROM `group` WHERE `gid`=?", $gid));

    if ($password == $groupPassword) {
      return DB::querySuccessful(DB::query("INSERT INTO `user_group` (`uid`, `gid`) VALUES (?, ?)", $_SESSION['uid'], $gid));
    }
    else {
      return false;
    }
  }

  public static function leave($gid) {
    Security::requireLoggedIn();
    
    return DB::querySuccessful(DB::query("UPDATE `user_group` SET `left`=? WHERE `uid`=? AND `gid`=?", 1, $_SESSION['uid'], $gid));
  }
}