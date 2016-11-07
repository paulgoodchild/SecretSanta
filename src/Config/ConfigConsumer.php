<?php

namespace Apto\Fun\SecretSanta\Config;

trait ConfigConsumer {

	/**
	 * @var VO
	 */
	protected $oConfig;

	/**
	 * @return VO
	 */
	public function getConfig() {
		return $this->oConfig;
	}

	/**
	 * @param VO $oConfig
	 * @return $this
	 */
	public function setConfig( VO $oConfig ) {
		$this->oConfig = $oConfig;
		return $this;
	}
}