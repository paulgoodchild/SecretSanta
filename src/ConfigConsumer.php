<?php

namespace Apto\Fun\SecretSanta;

trait ConfigConsumer {

	/**
	 * @var Config
	 */
	protected $oConfig;

	/**
	 * @return Config
	 */
	public function getConfig() {
		return $this->oConfig;
	}

	/**
	 * @param Config $oConfig
	 * @return $this
	 */
	public function setConfig( Config $oConfig ) {
		$this->oConfig = $oConfig;
		return $this;
	}
}