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
	 * The people TO whom this Person will give gives.
	 * @var Person[]
	 */
	protected $aFinalReceivers;

	/**
	 * The people FROM whom this Person will give gives.
	 * @var Person[]
	 */
	protected $aFinalGivers;

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
	public function getFinalGivers(): array {
		if ( !isset( $this->aFinalGivers ) ) {
			$this->aFinalGivers = array();
		}
		return $this->aFinalGivers;
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
	 *
	 * @return int
	 */
	public function getReceiveCount(): int {
		return count( $this->getFinalGivers() );
	}

	/**
	 * Note: Can also assign the Giver to the Receiver.
	 *
	 * @param Person $oReceiver
	 * @return Person
	 */
	public function assignReceiver( Person $oReceiver, $bSetConverse = false ) {
		if ( $bSetConverse ) {
			$oReceiver->assignGiver( $this, false );
		}

		$aReceivers = $this->getFinalReceivers();
		$aReceivers[] = $oReceiver;
		$this->aFinalReceivers = $aReceivers;
		return $this->removePotentialReceiver( $oReceiver );
	}

	/**
	 * Note: Can also assign the Receiver to the Giver.
	 *
	 * @param Person $oGiver
	 * @param bool   $bSetConverse
	 * @return $this
	 */
	public function assignGiver( Person $oGiver, $bSetConverse = false ) {
		if ( $bSetConverse ) {
			$oGiver->assignReceiver( $this, false );
		}
		$aGivers = $this->getFinalGivers();
		$aGivers[] = $oGiver;
		$this->aFinalGivers = $aGivers;
		return $this;
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