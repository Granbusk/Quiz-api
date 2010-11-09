<?php
class Group {

  public static function create($name, $password) {
    Security::requireLoggedIn();

    if (strlen(trim($name)) < 4) {
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
    return array(
      'admin' => DB::fetchField(DB::query("SELECT COUNT(*) FROM `user_group` WHERE `gid`=? AND `uid`=? AND `administrator`=?", $gid, $_SESSION['uid'], 1)),
      'users' => DB::fetchAll(DB::query("SELECT `u`.`uid`, `u`.`name`, `u`.`gravatar`, `ug`.`administrator`, `ug`.`moderator` FROM `user` `u`, `user_group` `ug` WHERE `ug`.`gid`=? AND `ug`.`uid`=`u`.`uid` AND `ug`.`left`=?", $gid, 0)),
    );    
  }

  public static function getById($gid) {
    $joined = DB::fetchField(DB::query("SELECT COUNT(*) FROM `user_group` WHERE `gid`=? AND `uid`=?", $gid, $_SESSION['uid']));

    if ($joined) {
      $group = DB::fetchArray(DB::query("SELECT `g`.`name`, `g`.`password`, `ug`.`administrator`, `ug`.`moderator`  FROM `group` `g`, `user_group` `ug` WHERE `ug`.`gid`=`g`.`gid` AND `ug`.`uid`=? AND `g`.`gid`=?", $_SESSION['uid'], $gid));

      $group['protected'] = $group['password'] != '';

      unset($group['password']);

      return $group;
    }

    return null;
  }

  public static function getTopList($gid, $limit) {
    $count = DB::fetchField(DB::query("SELECT COUNT(*) FROM `question_group` WHERE `gid`=?", $gid));
    
    $users = DB::fetchAll(DB::query(
      "SELECT
         `u`.`uid`,
         `u`.`name`,
      (SELECT COUNT(*)
         FROM
           `user_answer` `ua`,
           `question_group` `qg`
         WHERE
           `ua`.`qid`=`qg`.`qid`
           AND `qg`.`gid`=`ug`.`gid`
           AND `ug`.`uid`=`ua`.`uid`
           AND `ua`.`correct`=?
       ) AS `points`
       FROM
         `user_group` `ug`,
         `user` `u`
       WHERE
         `ug`.`uid`=`u`.`uid`
         AND `ug`.`gid`=?
       ORDER BY
         `points` DESC LIMIT " . $limit, 1, $gid));

    return array(
      'count' => $count,
      'users' => $users,
    );
  }

  public static function getMine() {
    Security::requireLoggedIn();
    
    return DB::fetchAll(DB::query("SELECT `g`.`gid`, `g`.`name` FROM `group` `g`, `user_group` `ug` WHERE `ug`.`uid`=? AND `ug`.`left`=? AND `ug`.`gid`=`g`.`gid`", $_SESSION['uid'], 0));
  }

  public static function userCanCreateQuestion($gid) {
    $isMod = DB::fetchField(DB::query("SELECT COUNT(*) FROM `user_group` WHERE `uid`=? AND `gid`=? AND `moderator`=?", $_SESSION['uid'], $gid, 1));
    
    return $isMod == 1;
  }

  public static function find($name) {
    if (strlen(trim($name)) < 3) {
      return false;
    }

    $groups = DB::fetchAll(DB::query("SELECT `gid`, `name`, `password` FROM `group` WHERE `name` LIKE '%" . $name . "%' LIMIT 20"));

    foreach ($groups as $key=>$group) {      
      $groups[$key]['protected'] = $group['password'] != '';
      
      unset($groups[$key]['password']);
    }

    return $groups;
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

  public static function setModerator($gid, $uid, $moderator) {
    Security::requireLoggedIn();
    
    if ($uid == $_SESSION['uid']) {
      return false;
    }

    $isAdmin = DB::fetchField(DB::query("SELECT COUNT(*) FROM `user_group` WHERE `gid`=? AND `uid`=? AND `administrator`=?", $gid, $_SESSION['uid'], 1));

    if ($isAdmin) {
      DB::query("UPDATE `user_group` SET `moderator`=? WHERE `gid`=? AND `uid`=?", $moderator, $gid, $uid);

      return true;
    }

    return false;
  }
}