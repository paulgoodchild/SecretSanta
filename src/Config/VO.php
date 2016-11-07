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
		$nLimit = (int)$this->getRules()[ 'attempts_limit' ];
		return ( $nLimit < 1 ) ? 1000 : $nLimit;
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
		return $this->getRawConfig()[ 'exclusion_sets' ];
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
		return $this->getRawConfig()[ 'rules' ];
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
		$aPeople = $this->getRawConfig()[ 'people' ];
		if ( $bShuffle ) {
			shuffle( $aPeople );
		}
		return $aPeople;
	}

	/**
	 * @return array
	 */
	public function getRequiredPeopleFields() {
		return $this->getRules()[ 'required_fields' ];
	}

	/**
	 * @return string
	 */
	public function getUniquePersonKey() {
		return $this->getRules()[ 'unique_key' ];
	}

	/**
	 * Use of this assumes the data has already been verified
	 *
	 * @return array
	 */
	public function getUniquePersonKeys() {
		$aPeople = $this->getPeople();
		$sUniqueKey = $this->getUniquePersonKey();
		$aAllUniqueKeys = array();
		foreach ( $aPeople as $nKey => $aPersonInfo ) {
			$aAllUniqueKeys[] = $aPersonInfo[ $sUniqueKey ];
		}
		return $aAllUniqueKeys;
	}

	/**
	 * @return array
	 */
	public function getRawConfig() {
		return $this->aConfig;
	}
}