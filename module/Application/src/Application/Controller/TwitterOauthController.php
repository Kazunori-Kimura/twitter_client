<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\TwitterOauth;

class TwitterOauthController extends AbstractActionController
{


    /**
     *
     */
    public function indexAction()
    {
        $twObj = new TwitterOauth();
        $msg = "";

        /* GET */
        $requestQuery = $this->params()->fromQuery();

        $ret = $twObj->twOauth( $requestQuery );

        if ( $ret === FALSE ) {
            $isErr = TRUE;
            $msg = 'トークン取得失敗';
        }
        else if ( isset($requestQuery['command']) ){
            /* ログイン */
            if ( $requestQuery['command'] === 'signin' ) {
                // 外部OAUTHサービスにリダイレクト
                header('Location: ' . $ret );
                exit;
            }
            /* サービスイン */
            if ( $requestQuery['command'] === 'callback' ) {
                // サービス提供URLにリダイレクト
                header('Location: http://sample.localhost/application/index/test'  );
                exit;
            }
            /* ログアウト */
            if ( $requestQuery['command'] === 'logout' ) {
                $msg = 'ログアウトしました';
                header('Location: http://sample.localhost/application/index/test'  );
                exit;
            }
            /* tweet */
            // ここでは廃止しました
        }
        // 例外
        header('Location: http://twap/'  );
        exit;
    }

}
