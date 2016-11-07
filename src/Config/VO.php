<?php

namespace Apto\Fun\SecretSanta\Config;

class VO {

	/**
	 * @var array
	 */
	protected $aConfig;

	public function __construct( $aConfig ) {
		$this->aConfig = $aConfig;
	}

	/**
	 * @return int
	 */
	public function getAttemptsLimit() {
		return $this->getRules()[ 'attempts_limit' ];
	}

	/**
	 * @return boolean
	 */
	public function getIfPrint() {
		return $this->getRules()[ 'print' ];
	}

	/**
	 * @return array
	 */
	public function getExclusionSets() {
		return $this->getConfig()[ 'exclusion_sets' ];
	}

	/**
	 * @return int
	 */
	public function getNumberOfPresentsEach() {
		return $this->getRules()[ 'presents_each' ];
	}

	/**
	 * @return array
	 */
	protected function getRules() {
		return $this->getConfig()[ 'rules' ];
	}

	/**
	 * @return int
	 */
	public function getTotalNumberOfPresents() {
		return $this->getNumberOfPresentsEach() * count( $this->getPeople() );
	}

	/**
	 * @param bool $bShuffle
	 * @return string[]
	 */
	public function getPeople( $bShuffle = true ) {
		$aPeople = $this->getConfig()[ 'people' ];
		if ( $bShuffle ) {
			shuffle( $aPeople );
		}
		return $aPeople;
	}

	/**
	 * @return array
	 */
	protected function getConfig() {
		return $this->aConfig;
	}
}