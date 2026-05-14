<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Abstracts\AjaxHandler;

class GetNotices extends AjaxHandler
{
    public function __construct()
    {
        parent::__construct('get_notices');
    }

    public function handler()
    {
        $notices = wc_get_notices('error');
        wc_clear_notices();

        $messages = array_values(array_filter(
            array_map(fn($n) => isset($n['notice']) ? wp_strip_all_tags($n['notice']) : '', $notices)
        ));

        wp_send_json_success(['messages' => $messages]);
    }
}

// Initialize handler
new GetNotices();
