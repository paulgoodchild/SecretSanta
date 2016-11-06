<?php

namespace Apto\Fun\SecretSanta;

class Person {

	/**
	 * @var string
	 */
	protected $sId;

	/**
	 * @var Person[]
	 */
	protected $aPotentialReceivers;

	/**
	 * @var Person[]
	 */
	protected $aFinalReceivers;

	/**
	 * @param string $sId
	 */
	public function __construct( string $sId ) {
		$this->sId = $sId;
	}

	/**
	 * @param array $aSetsOfExclusions
	 * @return $this
	 */
	public function processExclusions( array $aSetsOfExclusions ) {
		foreach ( $aSetsOfExclusions as $aExclusionSet ) {
			if ( in_array( $this->getId(), $aExclusionSet ) ) {
				foreach ( $aExclusionSet as $sId ) {
					$this->removePotentialReceiverById( $sId );
				}
			}
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->sId;
	}

	/**
	 * @return Person[]
	 */
	public function getFinalReceivers(): array {
		if ( !isset( $this->aFinalReceivers ) ) {
			$this->aFinalReceivers = array();
		}
		return $this->aFinalReceivers;
	}

	/**
	 * @return int
	 */
	public function getFinalReceiversCount() {
		return count( $this->getFinalReceivers() );
	}

	/**
	 * @return Person[]
	 */
	public function getPotentialReceivers(): array {
		if ( !isset( $this->aPotentialReceivers ) ) {
			$this->aPotentialReceivers = array();
		}
		return $this->aPotentialReceivers;
	}

	/**
	 * @return int
	 */
	public function getPotentialReceiversCount(): int {
		return count( $this->getPotentialReceivers() );
	}

	/**
	 * @param Person $oReceiver
	 * @return Person
	 */
	public function setFinalReceiver( Person $oReceiver ) {
		$aReceivers = $this->getFinalReceivers();
		$aReceivers[] = $oReceiver;
		$this->aFinalReceivers = $aReceivers;
		return $this->removePotentialReceiver( $oReceiver );
	}

	/**
	 * @param Person[] $aPotentialReceivers
	 * @return $this
	 */
	public function setPotentialReceivers( array $aPotentialReceivers ) {
		$this->aPotentialReceivers = $aPotentialReceivers;
		$this->removeMyselfFromPotentialReceivers();
		return $this;
	}

	/**
	 * @return $this
	 */
	protected function removeMyselfFromPotentialReceivers() {
		return $this->removePotentialReceiver( $this );
	}

	/**
	 * @param Person $oPerson
	 * @return Person
	 */
	public function removePotentialReceiver( $oPerson ) {
		return $this->removePotentialReceiverById( $oPerson->getId() );
	}

	/**
	 * @param string $sId
	 * @return $this
	 */
	protected function removePotentialReceiverById( string $sId ) {
		foreach ( $this->aPotentialReceivers as $nKey => $oReceiver ) {
			if ( $oReceiver->getId() == $sId ) {
				unset( $this->aPotentialReceivers[ $nKey ] );
			}
		}
		return $this;
	}

	public function __toString() {
		return $this->getId();
	}
}