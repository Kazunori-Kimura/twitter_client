<?php

namespace Application\Model;

class Twitter
{


    /**
     * コンストラクター
     */
    public function __construct()
    {

    }

    /**
     * 新規ユーザー登録
     * @param unknown_type $id
     * @param unknown_type $type
     */
    public function register( $id, $type='twitter')
    {
        $sql = "INSERT INTO twitter (oauth_site, user_id, reg_datetime) VALUES (?,?,?)";

        $value = array(
            $type,
            $userId,
            date('Y-m-d H:i:s'),
        );
    }

    /**
     * 登録済ユーザーチェック
     * @param unknown_type $id
     */
    public function searchId( $id )
    {
        $sql = "SELECT * FROM twitter WHERE user_id = ?";

    }


}