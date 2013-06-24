<?php
/**
 * Twitter OAUTH
 */

namespace Application\Model;

use Zend\Session\Container;

class TwitterOauth
{
    /* ツイッターログイン用アプリケーションキーセット */
    const CONSUMER_KEY    = 'lVol49LVPItmOFrHNYNig';
    const CONSUMER_SECRET = 'HvkzlaPRgFpqMXAnTi9ykGyGZiWrVMU0u0KKXsfZ8';

    const OAUTH_STAGE_REQUEST = 1;
    const OAUTH_STAGE_ACCESS  = 2;


    public  $twitterSession;

    private $_twitter;
    private $_twitterUtilObj;

    public function __construct()
    {
        /*
         * Zendセッション
         */
        $this->twitterSession = new Container('Application');

        /*
         * TwitterOAUTHの初期化
         */
        $this->_twitter = new tmhOAuth(
            array(
                "consumer_key"    => self::CONSUMER_KEY,
                "consumer_secret" => self::CONSUMER_SECRET,
            )
        );

        /*
         * ツイッターユーティリティ
         */
        $this->_twitterUtilObj = new tmhUtilities();

    }


    /**
     * ツイッターOAUTH実行
     * @param string $command
     * @return mixed
     */
    public function twOauth( $requestQuery )
    {
        var_dump($requestQuery);

        if ( !isset($requestQuery['command'])) return; // commandがないなら返却

        /* リクエストトークン取得 */
        if ( $requestQuery['command'] === 'signin') {
            // リクエストトークン取得
            $requestToken = $this->_getRequestTwOauth();
            var_dump($requestToken);
            // 取得失敗時
            if ( $requestToken === FALSE ) return FALSE;

            echo "リクエストトークン取得OK";

            //debug
            var_dump($requestToken);
            // セッションに格納
            $this->twitterSession->request_token = $requestToken;
            //Twitter認可画面リダイレクトURL生成
            $redirectUrl = $this->_twitter->url('oauth/authenticate', '') . '?oauth_token='.$requestToken['oauth_token'];
            return $redirectUrl;
        }
        /* アクセストークン取得 */
        if ( $requestQuery['command'] === 'callback' ) {
            /* セッションに格納したリクエストトークンをセット */
            $requestToken = $this->twitterSession->request_token;
            /* リクエストトークン破棄 */
            unset($this->twitterSession->request_token);
            /* アクセストークン取得 */
            $accessToken = $this->_getAccessTwOauth( $requestToken, $requestQuery );
            // 取得失敗時
            if ( $accessToken === FALSE ) return FALSE;

            /* 他のモデルを利用する */
            // TODO 実装

            // セッションに格納
            $this->twitterSession->access_token = $accessToken;
            return TRUE;
        }
        /* ログアウト */
        if ( $requestQuery['command'] === 'logout' ) {
            //セッションに格納されているアクセストークンを破棄してログアウト
            unset($this->twitterSession->access_token);
            //セッションクッキー削除
            setcookie( session_name(), "", time() - 3600 );
            return TRUE;
        }
    }


    /**
     * ツイッターOAUTH認証：リクエストトークン取得
     * @return string
     */
    private function _getRequestTwOauth()
    {
        /* リクエストトークン取得 */
        $here = $this->_twitterUtilObj->php_self();

        $this->_twitter->request(
            'POST',
            $this->_twitter->url( 'oauth/request_token', '' ),
            array('oauth_callback' => $here.'?command=callback')
        );
        // リクエストトークン
        $requestToken = $this->_twitter->extract_params( $this->_twitter->response['response'] );
        // 値の検証
        if ( $this->_validateToken($requestToken) === FALSE ) return FALSE;
        return $requestToken;
    }


    /**
     * ツイッターOAUTH認証：アクセストークン取得
     * @return Ambigous <multitype:, multitype:Ambigous <> >
     */
    private function _getAccessTwOauth( $token, $requestQuery )
    {
        /* リクエストトークンをセット */
        $this->_twitter->config['user_token'] = $token['oauth_token'];
        $this->_twitter->config['user_secret'] = $token['oauth_token_secret'];

        /* アクセストークン取得 */
        $this->_twitter->request(
            "POST",
            $this->_twitter->url("oauth/access_token", ""),
            array("oauth_verifier" => $requestQuery['oauth_verifier'] )
        );
        // アクセストークン
        $accessToken = $this->_twitter->extract_params( $this->_twitter->response['response'] );
        // 値の検証
        if ( $this->_validateToken($accessToken) === FALSE ) return FALSE;
        return $accessToken;
    }


    /**
     * ツイート
     * @param unknown_type $tweet
     */
    public function tweet( $tweet )
    {
        $accessToken = $this->twitterSession->access_token;

        /* アクセストークンをセット */
        $this->_twitter->config['user_token']  = $accessToken['oauth_token'];
        $this->_twitter->config['user_secret'] = $accessToken['oauth_token_secret'];
        $statusCode = $this->_twitter->request(
            "POST",
            $this->_twitter->url("1.1/statuses/update"),
            array("status" => $tweet)
        );
        /*
         * ツイート結果判定
         */
        // 成功
//         if ( $statusCode == 200 ) {
//             return TRUE;
//         }
        // 連続投稿エラー
//         else if ( $statusCode == 403 ) {
//             return FALSE;
//         }

        return $statusCode;
    }


    /**
     * トークンを取得できたかの検証
     * @param unknown_type $token
     * @param unknown_type $stage
     */
    private function _validateToken( $token, $stage = null )
    {
        if ( $stage === null ) {
            if ( isset($token['oauth_token']) && isset($token['oauth_token_secret']) ) {
                return TRUE;
            }
        }
        if ( $stage === self::OAUTH_STAGE_REQUEST ) {
            if ( isset($token['oauth_token']) && isset($token['oauth_token_secret']) ) {
                return TRUE;
            }
        }
        if ( $stage === self::OAUTH_STAGE_ACCESS ) {
            if ( isset($token['oauth_token']) && isset($token['oauth_token_secret']) ) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
