<?php

namespace Apto\Fun\SecretSanta;

use Apto\Fun\SecretSanta\Components\Person;
use Apto\Fun\SecretSanta\Config\ConfigConsumer;

class Lottery {

	use ConfigConsumer;

	/**
	 * @var Person[]
	 */
	protected $aEveryone;

	/**
	 * @var Person[]
	 */
	protected $aWorkingGiversPool;

	/**
	 * @return $this
	 */
	public function run() {
		// Verify there are enough remaining potentials for each person
		if ( !$this->verifyMinimumPresentsPossible() ) {
			echo 'Impossible: The minimum number of presents can never be met with the given exclusion sets.';
		}
		else {
			$nAttempts = 1;
			while ( $nAttempts <= $this->getConfig()->getAttemptsLimit() ) {
				try {
					$this->attemptAssignment();
					break;
				}
				catch ( \Exception $oE ) {
					$this->aEveryone = null;
					$nAttempts++;
				}
			}
		}
		return $this;
	}

	protected function attemptAssignment() {

		$nRunningTotalPresents = 0;
		$nTotalPresents = $this->getConfig()->getTotalNumberOfPresents();
		while( $nRunningTotalPresents < $nTotalPresents ) {

			$oNextGiver = $this->findNextPersonToBeGiver();
			$oNextReceiver = $this->pickRandomReceiverFromGiverPotentials( $oNextGiver );
			$oNextGiver->assignReceiver( $oNextReceiver, true );

			if ( $this->getIfGiverHasReachedAssignmentLimit( $oNextGiver ) ) {
				$this->removeGiverFromPool( $oNextGiver );
			}

			if ( $this->getIfReceiverHasReachedAssignmentLimit( $oNextReceiver ) ) {
				// remove this person from everyone else's potential matches as they've reached the limit
				$this->removeReceiverFromAllPotentials( $oNextReceiver );
			}

			$nRunningTotalPresents++;
//			var_dump( 'Current total presents: '.$nRunningTotalPresents );
		}
	}

	/**
	 * @param Person $oGiver
	 * @return Person
	 */
	protected function pickRandomReceiverFromGiverPotentials( Person $oGiver ) {
		$aPotentials = $oGiver->getPotentialReceivers();
		return $aPotentials[ array_rand( $aPotentials ) ];
	}

	/**
	 * @return Person[]
	 */
	public function getEveryone() {
		if ( !isset( $this->aEveryone ) ) {
			$oConfig = $this->getConfig();

			$this->aEveryone = array();
			$sUniqueKey = $oConfig->getUniquePersonKey();
			foreach ( $this->getConfig()->getPeople() as $nKey => $aPerson ) {
				$aPerson[ 'id' ] = $aPerson[ $sUniqueKey ];
				$oPerson = new Person( $aPerson );
				$this->aEveryone[ $oPerson->getId() ] = $oPerson;
			}

			$aExclusions = $oConfig->getExclusionSets();
			foreach ( $this->aEveryone as $oPerson ) {
				$oPerson
					->setPotentialReceivers( $this->aEveryone )
					->processExclusions( $aExclusions );
			}
			$this->aWorkingGiversPool = $this->aEveryone;
		}
		return $this->aEveryone;
	}

	/**
	 * @return Person[]
	 */
	protected function getWorkingGiversPool() {
		if ( empty( $this->aEveryone ) ) {
			$this->aWorkingGiversPool = $this->getEveryone();
		}
		return $this->aWorkingGiversPool;
	}

	/**
	 * @param Person $oGiver
	 * @return bool
	 */
	protected function getIfGiverHasReachedAssignmentLimit( Person $oGiver ) {
		return ( $oGiver->getFinalReceiversCount() == $this->getConfig()->getNumberOfPresentsEach() );
	}

	/**
	 * @param Person $oGiver
	 * @return $this
	 */
	protected function removeGiverFromPool( Person $oGiver ) {
		unset( $this->aWorkingGiversPool[ $oGiver->getId() ] );
		return $this;
	}

	/**
	 * @param Person $oReceiver
	 * @return bool
	 */
	protected function getIfReceiverHasReachedAssignmentLimit( Person $oReceiver ) {
		return ( $oReceiver->getReceiveCount() == $this->getConfig()->getNumberOfPresentsEach() );
	}

	/**
	 * @param Person $oReceiver
	 * @return $this
	 */
	protected function removeReceiverFromAllPotentials( Person $oReceiver ) {
		foreach( $this->getWorkingGiversPool() as $sGiver => $oPerson ) {
			$oPerson->removePotentialReceiver( $oReceiver );
		}
		return $this;
	}

	/**
	 * Selects a person who has the lowest, or one of the lowest, selection of potential matches. It reduces the chances
	 * slightly of dead-ends later on.
	 *
	 * @return Person
	 * @throws \Exception
	 */
	protected function findNextPersonToBeGiver() {
		/** @var Person $oCurrentPerson */
		$oCurrentPerson = null;
		foreach( $this->getWorkingGiversPool() as $sPerson => $oPerson ) {
			if ( $oPerson->getPotentialReceiversCount() == 0 ) {
				throw new \Exception( 'Reached a stage when user has no potential matches' );
			}
			if ( is_null( $oCurrentPerson )
				|| $oPerson->getPotentialReceiversCount() < $oCurrentPerson->getPotentialReceiversCount() ) {
				$oCurrentPerson = $oPerson;
			}
		}
		return $oCurrentPerson;
	}


	/**
	 * @return bool
	 */
	protected function verifyMinimumPresentsPossible() {
		$nNumberOfPresents = $this->getConfig()->getNumberOfPresentsEach();
		$aPeoplePotentials = $this->getEveryone();
		foreach ( $aPeoplePotentials as $oPerson ) {
			if ( $oPerson->getPotentialReceiversCount() < $nNumberOfPresents ) {
				return false;
			}
		}
		return true;
	}
}