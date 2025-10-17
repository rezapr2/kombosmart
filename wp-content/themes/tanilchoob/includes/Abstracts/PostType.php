<?php

namespace TanilChoob\Theme\Abstracts;

abstract class PostType {
	abstract protected function register();

	public function __construct() {
		add_action( 'init', [$this, 'register']);
	}
}