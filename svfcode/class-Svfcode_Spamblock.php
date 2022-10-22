<?php

class SvfcodeSpamblock {

    /**
     * @var SvfcodeSpamblock
     */
    private static $inst;

    /**
     * @var string
     */
    private $nonce = '';

    public static function instance()
    {
        self::$inst || self::$inst = new self();
        return self::$inst;
    }

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if( ! wp_doing_ajax() && ! is_admin() ) {
            add_action( 'comment_form', [ $this, 'mainJs' ], 99 );
        }

        add_action("wp_ajax_get_api_user_ip", [ $this, 'getApiUserIp' ]);
        add_action("wp_ajax_nopriv_get_api_user_ip", [ $this, 'getApiUserIp' ]);

        add_filter( 'preprocess_comment', [ $this, 'blockSpam' ], 0 );

        $this->nonce = wp_create_nonce('get_api_user_ip');;
    }

    /**
     * Check and block comment if needed.
     *
     * @return array
     */
    public function blockSpam($commentdata)
    {
        if(!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'get_api_user_ip') ) {
            wp_die( $this->blockForm() );
        }

        if (isset($_POST['svfcode_spamblock_client_ip']) && $commentdata["comment_author_IP"] !== $_POST["svfcode_spamblock_client_ip"]) {
            wp_die( $this->blockForm() );
        }

        return $commentdata;
    }

    public function mainJs()
    {
        global $post;

        if( !empty( $post ) && 'open' !== $post->comment_status && is_singular() ) {
           return;
        }

        wp_enqueue_script( 'svfcode_spamblock_script', plugin_dir_url( __FILE__ ) . 'assets/js/script.js' );

        $data = [
            "url_get_ip" => admin_url('admin-ajax.php'),
            "ajax_nonce" => $this->nonce
        ];

        wp_localize_script(
            'svfcode_spamblock_script',
            'ajax_data',
            $data
        );
    }

    public function getApiUserIp()
    {
        if(!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'get_api_user_ip') ) {
            wp_send_json_error();
        }

        $ip = null;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if ( ! is_null($ip) ) {
            wp_send_json_success([
                'success' => true,
                'ip' => $ip,
                'nonce' => $this->nonce
            ]);
        }

        wp_die();
    }

    /*
     * Output form when comment has been blocked.
     */
    private function blockForm()
    {
        ?>
            <h1><?= __( 'Antispam block your comment!', 'svfcode-spamblock' ) ?></h1>
            <p>
                <a href="javascript:history.back()">← Назад</a>
            </p>
        <?php
    }
}
