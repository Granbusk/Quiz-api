<?php

abstract class DB {
  private static $dbh;
  private static $lastInsertId;

  public function  __construct() {}

  private function dbInit() {
    global $settings;
    
    if (!isset(self::$dbh)) {
      self::$dbh = new PDO('mysql:host=' . $settings['db-host'] . ';dbname=' . $settings['db-name'], $settings['db-user'], $settings['db-pass'], array(PDO::ATTR_PERSISTENT => true));
    }
  }

  public static function query() {    
    self::dbInit();

    $args = func_get_args();

    $query = array_shift($args);

    $args = is_array($args) ? $args : array();

    if (count($args) > 0 && is_array($args[0])) {
      $args = array_values($args[0]);
    }

    $stmt = self::$dbh->prepare($query);

    $stmt->execute($args);

    //print_r($stmt->errorInfo());

    self::$lastInsertId = self::$dbh->lastInsertId();

    return $stmt;
  }

  public static function fetchAll($stmt) {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
  }

  public static function fetchArray($stmt, $mode=PDO::FETCH_ASSOC) {
    return $stmt->fetch($mode);
  }

  public static function fetchField($stmt) {
    $row = self::fetchArray($stmt);

    if ($row == null) {
      return null;
    }

    return implode('', $row);
  }

  public static function querySuccessful($stmt) {

    return $stmt->errorCode() == '00000';
  }

  public static function getInsertId() {
    $id = self::$lastInsertId;

    self::$lastInsertId = null;

    return $id;
  }
}