<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\TwitterOauth;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function testAction()
    {
        $twObj = new TwitterOauth();

        /* セッション値 */
        $sessionValues = $twObj->twitterSession->access_token;

        /* GET */
        $requestQuery = $this->params()->fromQuery();

        /* POST */
        $tweet = $this->params()->fromPost('tweet');

        $msg = "";
        $result = "";

        /* tweet */
        if ( isset($requestQuery['command']) && $requestQuery['command'] === 'tweet' ) {
            $reCode = $twObj->tweet( $tweet );
            $result = $reCode.':'.$tweet;
        }

        /* ビューに渡す値 */
        $array = array(
            'twSession' => $sessionValues,
            'msg'       => $msg,
            'userName'  => $sessionValues['screen_name'],
            'userId'    => $sessionValues['user_id'],
            'result'    => $result,
        );
        return $array;
    }

    public function hogeAction()
    {
    	$foo = "foo";
    	$array = array(
    		'foo' => $foo,
    	);
    	return $array;
    }

}
