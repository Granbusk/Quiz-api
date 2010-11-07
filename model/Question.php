<?php
class Question {

  public static function add($question, $alternatives, $categories=array()) {
    $qid = DB::getInsertId(DB::query("INSERT INTO question (uid, question) VALUES (?, ?)", $_SESSION['uid'], $question));

    foreach ($alternatives as $alt) {
      DB::query("INSERT INTO alternative (qid, alternative, correct) VALUES (?, ?, ?)", $qid, $alt['alternative'], $alt['correct']);
    }

    foreach ($categories as $cid) {
      DB::query("INSERT INTO question_category (qid, cid) VALUES (?, ?)", $qid, $cid);
    }
  }

  public static function get($categories=array()) {    
    if (count($categories) > 0) {
      $question = DB::fetchArray(DB::query("SELECT q.qid, q.question FROM question q, question_category qc WHERE q.approved=? AND q.removed=? AND qc.cid IN (" . implode(',', $categories) . ") AND q.qid=qc.qid AND  AND q.qid NOT IN (SELECT ua.qid FROM user_answer ua WHERE ua.uid=?) ORDER BY RAND() LIMIT 1", 1, 0, $_SESSION['uid']));
    }
    else {
      $question = DB::fetchArray(DB::query("SELECT q.qid, q.question FROM question q WHERE q.approved=? AND q.removed=? AND q.qid NOT IN (SELECT ua.qid FROM user_answer ua WHERE ua.uid=?) ORDER BY RAND() LIMIT 1", 1, 0, $_SESSION['uid']));
    }

    $alternatives = DB::fetchAll(DB::query("SELECT aid, alternative FROM alternative WHERE qid=?", $question['qid']));

    return array(
      'question' => $question,
      'alternatives' => $alternatives,
    );
  }

  public static function answer($qid, $aid) {
    $correct = DB::fetchField(DB::query("SELECT correct FROM alternative WHERE qid=? AND aid=?", $qid, $aid));
    
    DB::query("INSERT INTO user_answer (uid, qid, correct) VALUES (?, ?, ?)", $_SESSION['uid'], $qid, $correct);

    return array(
      'correct' => $correct == 1,
    );
  }

}