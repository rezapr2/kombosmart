<?php

namespace TanilChoob\Theme\GutenbergBlock;

use TanilChoob\Theme\Bootstrap;

class GutenbergBlocks
{
	private $needsWrapperBlocks = [
		// Add block name here to add container wrapper
		// 'core/paragraph'
	];

    public function __construct() {
        $this->load_dependencies();

        add_filter('render_block', [$this, 'blocks_wrapper'], 10, 2);
    }

    private function load_dependencies() {
        foreach ( glob( Bootstrap::$path . 'includes/GutenbergBlock/*.php' ) as $filename ) {
            if($filename != "GutenbergBlocks.php")
                include_once $filename;
        }
    }

	public function blocks_wrapper( $block_content, $block ) {
		$isCoreBlock = strpos($block['blockName'], 'core') === 0;
		$isFullWidth = false;
		if( array_key_exists("className", $block["attrs"]) ) {
			$isFullWidth = strpos($block["attrs"]["className"], 'container-fluid') !== false;
		}

		if( (is_page() || is_single()) && (in_array($block['blockName'], $this->needsWrapperBlocks) || $isCoreBlock) && !$isFullWidth ) {
			$content = '<div class="container"><div class="row"><div class="col-12" data-aos="fade" data-aos-duration="700" data-aos-delay="200" data-aos-once="true">';
			$content .= $block_content;
			$content .= '</div></div></div>';
			return $content;
		}

		return $block_content;
    }

}