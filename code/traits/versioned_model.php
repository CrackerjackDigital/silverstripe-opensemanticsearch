<?php

namespace OpenSemanticSearch\Traits;

trait versioned_model {
	abstract function add( $item );

	abstract function metadata( $item );

	abstract function remove( $item );

	abstract function OSSID();

	/**
	 * @return \Page
	 */
	public function Publish() {
		return $this->owner;
	}

	public function onBeforePublish() {
		$this->remove( $this->owner() );
	}

	public function onAfterPublish() {
		$this->add( $this->owner());
		$this->metadata($this->owner());
	}

	public function onAfterUnpublish() {
		$this->remove($this->owner());
	}
}