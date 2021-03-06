<?php @session_start();

require_once 'settings.php';

require_once 'util/DB.php';
require_once 'util/Security.php';
require_once 'util/ArrayToXML.php';

require_once 'model/Group.php';
require_once 'model/Question.php';
require_once 'model/User.php';

$params = explode('/', $_GET['query']);

Security::sanitize($params);
Security::sanitize($_POST);

$model = array_shift($params);
$action = array_shift($params);

$response = null;

switch ($model) {
  case 'user':
    switch ($action) {
      case 'signup':
        $user = new User($_POST['name'], $_POST['email'], $_POST['password']);

        $response = $user->signup();

        if ($response) {
          User::login($_POST['email'], $_POST['password']);
        }        
        break;

      case 'login':
        $response = User::login($_POST['email'], $_POST['password']);        
        break;

      case 'update':
        $user = new User($_POST['name'], $_POST['email'], $_POST['epass'], $_POST['gravatar']);
        $response = $user->update($_POST['npass']);
        break;

      case 'logout':
        $response = User::logout();
        break;

      case 'check':
        $response = array_key_exists('uid', $_SESSION) && is_numeric($_SESSION['uid']);
        break;

      case 'get':
        $response = User::get(0, false);
        break;
    }    
    break;

  case 'group':
    switch ($action) {
      case 'create':
        $_POST['name'] = array_key_exists('name', $_POST) ? $_POST['name'] : '';
        $_POST['password'] = array_key_exists('password', $_POST) ? $_POST['password'] : '';
        $_POST['description'] = array_key_exists('description', $_POST) ? $_POST['description'] : '';

        $response = Group::create($_POST['name'], $_POST['password'], $_POST['description']);
        break;

      case 'find':
        $response = Group::find($_POST['name']);
        break;

      case 'join':
        $_POST['password'] = array_key_exists('password', $_POST) ? $_POST['password'] : '';        
        $response = Group::join($_POST['gid'], $_POST['password']);
        break;

      case 'leave':
        $response = Group::leave($_POST['gid']);
        break;

      case 'get':
        switch ($params[0]) { 
          case 'mine':
            $response = Group::getMine();
            break;

          case 'toplist':
            $response = Group::getTopList($_POST['gid'], $_POST['limit']);
            break;

          case 'contributors':
            $params[1] = count($params) >= 2 ? $params[1] : null;
            $response = Group::getContributors($params[1]);
            break;


          case 'overview':
            $response = Group::getOverview($_POST['gid']);
            break;
          
          default:
            if (is_numeric($params[0])) {
              $response = Group::getById($params[0]);
            }
        }
        break;

      case 'setmod':
        $response = Group::setModerator($_POST['gid'], $_POST['uid'], $_POST['mod']);
        break;
    }
    break;

  case 'question':
    switch ($action) {
      case 'add':
        $_POST['question'] = array_key_exists('question', $_POST) ? $_POST['question'] : '';        
        $_POST['correct'] = array_key_exists('correct', $_POST) ? $_POST['correct'] : null;
        $_POST['answer-explanation'] = array_key_exists('answer-explanation', $_POST) ? $_POST['answer-explanation'] : '';

        $alternatives = array();
        for ($i=0; $i<4; $i++) {
          $alternatives[] = $_POST['alt-' . $i];
        }
          
        $response = Question::add($_POST['question'], $alternatives, $_POST['correct'], $_POST['answer-explanation'], $_POST['gid']);
        break;

      case 'get':
        $response = Question::get($params[0], $params[1]);        
        break;

      case 'my':
        $params[0] = count($params) >= 1 ? $params[0] : null;        
        $response = Question::getMine($params[0]);
        break;

      case 'answer':
        $_POST['qid'] = array_key_exists('qid', $_POST) ? $_POST['qid'] : '';
        $_POST['aid'] = array_key_exists('aid', $_POST) ? $_POST['aid'] : '';
        $response = Question::answer($_POST['qid'], $_POST['aid']);
        break;
    }
    break;
  // end $model
}

header(200);
header('Content-type: application/xml');
print is_array($response) ? ArrayToXML::toXml($response, 'data') : '<data>' . $response . '</data>';
exit;