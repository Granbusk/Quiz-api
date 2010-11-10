<?php
class Question {

  public static function add($question, $alternatives, $correct, $answerExplanation, $gid) {
    Security::requireLoggedIn();

    if (!Group::userCanCreateQuestion($gid)) {
      return false;
    }

    if (strlen($question) == 0 || is_null($correct)) {
      return false;
    }

    DB::query("INSERT INTO `question` (`uid`, `text`, `answer_explanation`) VALUES (?, ?, ?)", $_SESSION['uid'], $question, $answerExplanation);
    
    $qid = DB::getInsertId();

    foreach ($alternatives as $key=>$text) {
      
      DB::query("INSERT INTO `alternative` (`qid`, `text`, `correct`) VALUES (?, ?, ?)", $qid, $text, $key == $correct ? 1 : 0);
    }

    DB::query("INSERT INTO `question_group` (`qid`, `gid`) VALUES (?, ?)", $qid, $gid);

    return true;
  }

  public static function get($gid, $prevQid) {
    if ($gid == 0) {
      $query =
       "SELECT
          `q`.`qid`,
          `q`.`text`
        FROM
          `question` `q`,
          `question_group` `qg`
        WHERE
          `q`.`removed`=?
          AND `q`.`qid`=`qg`.`qid`
          AND `q`.`qid` NOT IN (SELECT `ua`.`qid` FROM `user_answer` `ua` WHERE `ua`.`uid`=?)
          AND `qg`.`gid` IN (SELECT `ug`.`gid` FROM `user_group` `ug` WHERE `ug`.`uid`=?)
          AND `q`.`qid`=`qg`.`qid`
        ORDER BY RAND() LIMIT 2
      ";

      $questions = DB::fetchAll(DB::query($query, 0, $_SESSION['uid'], $_SESSION['uid']));
    }
    else {
      $query =
       "SELECT
          `q`.`qid`,
          `q`.`text`
        FROM
          `question` `q`,
          `question_group` `qg`
        WHERE
          `q`.`removed`=?
          AND `q`.`qid`=`qg`.`qid`
          AND `q`.`qid` NOT IN (SELECT `ua`.`qid` FROM `user_answer` `ua` WHERE `ua`.`uid`=?)
          AND `qg`.`gid`=?
          AND `q`.`qid`=`qg`.`qid`
        ORDER BY RAND() LIMIT 2
      ";

      $questions = DB::fetchAll(DB::query($query, 0, $_SESSION['uid'], $gid));
    }

    if (count($questions) == 0) {
      return null;
    }

    $question = $questions[0]['qid'] == $prevQid && count($questions) > 1 ? $questions[1] : $questions[0];
    
    $alternatives = DB::fetchAll(DB::query("SELECT `aid`, `text` FROM `alternative` WHERE `qid`=?", $question['qid']));

    return array(
      'question' => $question,
      'alternatives' => $alternatives,
    );
  }

  public static function answer($qid, $aid) {
    Security::requireLoggedIn();
    
    $correct = DB::fetchField(DB::query("SELECT `correct` FROM `alternative` WHERE `qid`=? AND `aid`=?", $qid, $aid));
    
    DB::query("INSERT INTO `user_answer` (`uid`, `qid`, `aid`, `correct`) VALUES (?, ?, ?, ?)", $_SESSION['uid'], $qid, $aid, $correct);

    $correct_answer = $correct == 0 ? DB::fetchField(DB::query("SELECT `aid` FROM `alternative` WHERE `qid`=? AND `correct`=?", $qid, 1)) : $aid;
    $answer_description = DB::fetchField(DB::query("SELECT `answer_explanation` FROM `question` WHERE `qid`=?", $qid));

    return array(
      'correct' => $correct == 1,
      'answer' => $correct_answer,
      'explanation' => $answer_description,
    );
  }
}