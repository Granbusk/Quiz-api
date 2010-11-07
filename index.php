<?php @session_start();

$_SESSION['uid'] = 1;

require_once 'settings.php';
require_once 'DB.php';
require_once 'ArrayToXML.php';

require_once 'model/User.php';
require_once 'model/Question.php';

list($model, $action) = explode('/', $_GET['query']);

$response = null;

switch ($model) {
  case 'user':
    switch ($action) {
      case 'signup':
        $user = new User($_POST['name'], $_POST['email'], $_POST['password']);

        if ($user->signup()) {
          $response = User::login($_POST['email'], $_POST['password']);
        }
        
        break;

      case 'login':
        $response = User::login($_POST['email'], $_POST['password']);
        
        break;

      case 'update':
        $user = new User($_POST['name'], $_POST['email'], $_POST['epass']);

        $response = $user->update($_POST['npass']);

        break;

      case 'logout':
        User::logout();

        break;
    }
    
    break;

  case 'question':
    switch ($action) {
      case 'add':
        $_POST['question'] = array_key_exists('question', $_POST) ? $_POST['question'] : '';
        $_POST['alternatives'] = array_key_exists('alternatives', $_POST) ? $_POST['alternatives'] : array();
        $_POST['categories'] = array_key_exists('categories', $_POST) ? $_POST['categories'] : array();

        Question::add($_POST['question'], $_POST['alternatives'], $_POST['categories']);

        break;

      case 'get':
        $_POST['categories'] = array_key_exists('categories', $_POST) ? $_POST['categories'] : array();
        
        $response = Question::get($_POST['categories']);

        break;

      case 'answer':
        $_POST['qid'] = array_key_exists('qid', $_POST) ? $_POST['qid'] : '';
        $_POST['cid'] = array_key_exists('cid', $_POST) ? $_POST['cid'] : '';

        $response = Question::answer($_POST['qid'], $_POST['cid']);

        break;
    }

    break;
  // end default
}

header(200);
header('Content-type: application/xml');
print is_array($response) ? ArrayToXML::toXml($response, 'data') : '<data>' . $response . '</data>';
exit;