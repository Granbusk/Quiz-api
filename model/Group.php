<?php

class Group {

  public static function create($name, $password, $description) {
    Security::requireLoggedIn();

    if (strlen(trim($name)) < 4) {
      return false;
    }

    DB::query("
      INSERT INTO
        `group` (`name`, `password`, `description`)
        VALUES (?, ?, ?)
      ", $name, $password, $description);

    $gid = DB::getInsertId();

    if ($gid > 0) {
      DB::query("
        INSERT INTO
          `user_group` (`uid`, `gid`, `moderator`, `administrator`)
          VALUES (?, ?, ?, ?)
        ", $_SESSION['uid'], $gid, 1, 1);
    }

    return $gid;
  }

  public static function getById($gid) {
    $joined = DB::fetchField(DB::query("
      SELECT
        COUNT(*)
      FROM
        `user_group`
      WHERE
        `gid`=?
        AND `uid`=?
      ", $gid, $_SESSION['uid']));

    if ($joined) {
      $group = DB::fetchArray(DB::query("
        SELECT
          `g`.`name`,
          `g`.`password`,
          `ug`.`administrator`,
          `ug`.`moderator`
        FROM
          `group` `g`,
          `user_group` `ug`
        WHERE
          `ug`.`gid`=`g`.`gid`
          AND `ug`.`uid`=?
          AND `g`.`gid`=?
          AND `ug`.`left`=?
        ", $_SESSION['uid'], $gid, 0));

      $group['protected'] = $group['password'] != '';

      unset($group['password']);

      return $group;
    }

    return null;
  }

  public static function getTopList($gid, $limit) {
    $count = DB::fetchField(DB::query("
      SELECT
        COUNT(*)
      FROM
        `question_group`
      WHERE
        `gid`=?
      ", $gid));

    $users = DB::fetchAll(DB::query("
      SELECT
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

  public static function getOverview($gid) {
    $stats = DB::fetchArray(DB::query("
      SELECT
        COUNT(*) AS `questions`,
        (SELECT COUNT(*)
          FROM
            `user_answer` `ua`,
            `question_group` `cqg`
          WHERE
            `ua`.`uid`=?
            AND `ua`.`qid`=`cqg`.`qid`
            AND `cqg`.`gid`=`qg`.`gid`
        ) AS `answers`
      FROM
        `question_group` `qg`
      WHERE `qg`.`gid`=?
      ", $_SESSION['uid'], $gid));

    $overview = DB::fetchAll(DB::query("
       SELECT
         `ua`.`aid`,
         `ua`.`correct`,
         `q`.`text` AS `question`,
         `a`.`text` AS `answer`,
        (SELECT
          `ca`.`text`
         FROM
          `alternative` `ca`
         WHERE
          `ca`.`qid`=`q`.`qid`
          AND `ca`.`correct`=?
         ) AS `correct_answer`,
         `q`.`answer_explanation`
       FROM
         `user_answer` `ua`,
         `alternative` `a`,
         `question` `q`,
         `question_group` `qg`
       WHERE         
         `qg`.`gid`=?
         AND `q`.`qid`=`qg`.`qid`
         AND `ua`.`qid`=`q`.`qid`
         AND `ua`.`uid`=?
         AND `ua`.`aid`=`a`.`aid`
     ", 1, $gid, $_SESSION['uid']));

    return array(
      'stats' => $stats,
      'overview' => $overview,
    );
  }

  public static function getMine() {
    Security::requireLoggedIn();

    return DB::fetchAll(DB::query("
      SELECT
        `g`.`gid`,
        `g`.`name`
      FROM
        `group` `g`,
        `user_group` `ug`
      WHERE
        `ug`.`uid`=?
        AND `ug`.`left`=?
        AND `ug`.`gid`=`g`.`gid`
      ", $_SESSION['uid'], 0));
  }

  public static function userCanCreateQuestion($gid) {
    $isMod = DB::fetchField(DB::query("
      SELECT
        COUNT(*)
      FROM
        `user_group`
      WHERE
        `uid`=?
        AND `gid`=?
        AND `moderator`=?
      ", $_SESSION['uid'], $gid, 1));

    return $isMod == 1;
  }

  public static function find($name) {
    if (strlen(trim($name)) < 3) {
      return false;
    }

    $groups = DB::fetchAll(DB::query("
      SELECT
        `gid`,
        `name`,
        `password`,
        `description`
      FROM
        `group`
      WHERE
        `name` LIKE '%" . $name . "%'
      LIMIT 20"));

    foreach ($groups as $key => $group) {
      $groups[$key]['protected'] = $group['password'] != '';

      unset($groups[$key]['password']);
    }

    return $groups;
  }

  public static function join($gid, $password) {
    Security::requireLoggedIn();

    $groupPassword = DB::fetchField(DB::query("
      SELECT
        `password`
      FROM
        `group`
      WHERE
        `gid`=?
      ", $gid));

    if ($password == $groupPassword) {
      DB::query("DELETE FROM `user_group` WHERE `uid`=? AND `gid`=?", $_SESSION['uid'], $gid);

      return DB::querySuccessful(DB::query("
        INSERT INTO
          `user_group` (`uid`, `gid`)
          VALUES (?, ?)
        ", $_SESSION['uid'], $gid));
    } else {
      return false;
    }
  }

  public static function getContributors($gid) {
    if (!is_null($gid)) {
      $contributors = DB::fetchAll(DB::query("
        SELECT
          `u`.`name`,
          (SELECT
            COUNT(*)
          FROM
            `question_group` `qg`,
            `question` `q`
          WHERE
            `qg`.`gid`=`ug`.`gid`
            AND `qg`.`qid`=`q`.`qid`
            AND `q`.`uid`=`u`.`uid`
          ) AS `question_count`
        FROM
          `user` `u`,
          `user_group` `ug`
        WHERE
          `ug`.`uid`=`u`.`uid`
          AND `ug`.`gid`=?
          AND `ug`.`moderator`=?
        ORDER BY
          `question_count` DESC
        ", $gid, 1));
    }
    else {
      $contributors = DB::fetchAll(DB::query("
        SELECT
          `u`.`name`,
          (SELECT
            COUNT(*)
          FROM
            `question` `q`
          WHERE
            `q`.`uid`=`u`.`uid`
          ) AS `question_count`
        FROM
          `user` `u`,
          `user_group` `ug`
        WHERE
          `ug`.`uid`=`u`.`uid`
          AND `moderator`=?
        ORDER BY
          `question_count` DESC", 1));
    }

    return $contributors;
  }

  public static function leave($gid) {
    Security::requireLoggedIn();

    return DB::querySuccessful(DB::query("
      UPDATE
        `user_group`
      SET
        `left`=?
      WHERE
        `uid`=?
        AND `gid`=?
      ", 1, $_SESSION['uid'], $gid));
  }

  public static function setModerator($gid, $uid, $moderator) {
    Security::requireLoggedIn();

    if ($uid == $_SESSION['uid']) {
      return false;
    }

    $isAdmin = DB::fetchField(DB::query("
      SELECT
        COUNT(*)
      FROM
        `user_group`
      WHERE
        `gid`=?
        AND `uid`=?
        AND `administrator`=?
      ", $gid, $_SESSION['uid'], 1));

    if ($isAdmin) {
      DB::query("
        UPDATE
          `user_group`
        SET
          `moderator`=?
        WHERE
          `gid`=?
          AND `uid`=?
        ", $moderator, $gid, $uid);

      return true;
    }

    return false;
  }

}