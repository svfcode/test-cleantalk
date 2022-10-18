<?php

class Svfcode_Spamblock {

    /**
     * @var Svfcode_Spamblock
     */
    private static $inst;

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
            add_action( 'wp_footer', [ $this, 'main_js' ], 99 );
        }

        add_action("wp_ajax_get_api_user_ip", [ $this, 'get_api_user_ip' ]);
        add_action("wp_ajax_nopriv_get_api_user_ip", [ $this, 'get_api_user_ip' ]);

        add_filter( 'preprocess_comment', [ $this, 'block_spam' ], 0 );
    }

    /**
     * Check and block comment if needed.
     *
     * @return array
     */
    public function block_spam($commentdata)
    {
        if ($commentdata["comment_author_IP"] !== $_POST["svfcode_spamblock_client_ip"]) {
            wp_die( $this->block_form() );
        }

        return $commentdata;
    }

    public function main_js()
    {
        global $post;

        if( !empty( $post ) && 'open' !== $post->comment_status && is_singular() ) {
           return;
        }

        ?>
            <script id="svfcode_spamblock">
                (function() {
                    fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=get_api_user_ip", {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                    }).then(function(response) {
                        return response.json()
                    }).then(function(data) {
                        if (data.data && data.data.success === true) {
                            let input = document.createElement("input");
                            input.setAttribute("type", "hidden");
                            input.setAttribute("name", "svfcode_spamblock_client_ip");
                            input.setAttribute("value", data.data.ip);
                            if (document.getElementById("commentform")) {
                                document.getElementById("commentform").appendChild(input);
                            }
                        }
                    }).catch(function(err) {

                    });
                })()
            </script>
        <?php
    }

    public function get_api_user_ip()
    {
        $ip = null;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if ( ! is_null($ip) ) {
            wp_send_json_error(['success' => true, 'ip' => $ip]);
        }

        wp_die();
    }

    /*
     * Output form when comment has been blocked.
     */
    private function block_form()
    {
        ob_start();
        ?>
            <h1><?= __( 'Antispam block your comment!', 'svfcode-spamblock' ) ?></h1>
            <p>
                <a href="javascript:history.back()">← Назад</a>
            </p>
        <?php
        return ob_get_clean();
    }
}
