<?php
class Question {

  public static function add($question, $alternatives, $groups) {
    Security::requireLoggedIn();
    
    $qid = DB::getInsertId(DB::query("INSERT INTO `question` (`uid`, `question`) VALUES (?, ?)", $_SESSION['uid'], $question));

    foreach ($alternatives as $alt) {
      DB::query("INSERT INTO `alternative` (`qid`, `alternative`, `correct`) VALUES (?, ?, ?)", $qid, $alt['alternative'], $alt['correct']);
    }

    foreach ($groups as $gid) {
      if (Group::userCanCreateQuestion($gid)) {
        DB::query("INSERT INTO `question_group` (`qid`, `gid`) VALUES (?, ?)", $qid, $gid);
      }
    }
  }

  public static function get($groups) {
    if (count($groups) > 0) {
      $query = "SELECT `q`.`qid`, `q`.`question` FROM `question` `q`, `question_groups` `qg` WHERE `q`.`removed`=? AND `qg`.`cid` IN (" . implode(',', $groups) . ") AND `q`.`qid`=`qg`.`qid` AND `q`.`qid` NOT IN (SELECT `ua`.`qid` FROM `user_answer` `ua` WHERE `ua`.`uid`=?) ORDER BY RAND() LIMIT 1";
    }
    else {
      $query = "SELECT `q`.`qid`, `q`.`question` FROM `question` `q` WHERE `q`.`removed`=? AND `q`.`qid` NOT IN (SELECT `ua`.`qid` FROM `user_answer` `ua` WHERE `ua`.`uid`=?) ORDER BY RAND() LIMIT 1";
    }

    $question = DB::fetchArray(DB::query($query, 0, $_SESSION['uid']));
    
    $alternatives = DB::fetchAll(DB::query("SELECT `aid`, `alternative` FROM `alternative` WHERE `qid`=?", $question['qid']));

    return array(
      'question' => $question,
      'alternatives' => $alternatives,
    );
  }

  public static function answer($qid, $aid) {
    Security::requireLoggedIn();
    
    $correct = DB::fetchField(DB::query("SELECT `correct` FROM `alternative` WHERE `qid`=? AND `aid`=?", $qid, $aid));
    
    DB::query("INSERT INTO `user_answer` (`uid`, `qid`, `correct`) VALUES (?, ?, ?)", $_SESSION['uid'], $qid, $correct);

    return array(
      'correct' => $correct == 1,
    );
  }
}