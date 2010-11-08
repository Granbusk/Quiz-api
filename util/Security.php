<?php
class Security {
  public static function sanitize(&$input) {
    if (is_array($input)) {
      foreach ($input as $field=>$value) {
        if (is_array($value)) {
          self::sanitize($value);
          return;
        }
        
        $input[$field] = htmlspecialchars(strip_tags($value));
      }
    }
    else {
      $input = htmlspecialchars(strip_tags($input));
    }
  }

  public static function requireLoggedIn() {
    if (!is_numeric($_SESSION['uid'])) {
      header(200);
      header('Content-type: application/xml');
      print '<data>not logged in</data>';
      exit;
    }
  }
}