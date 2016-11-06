<?php

namespace Apto\Fun\SecretSanta;

class Secret {

	use ConfigConsumer;

	/**
	 * @var []
	 */
	protected $aBaselinePotentialMatches;

	/**
	 * @var []
	 */
	protected $aFinalPeopleMatches;

	/**
	 * @var []
	 */
	protected $aCountPresentsForReceivers;

	/**
	 * @var []
	 */
	protected $aCountPresentsForGivers;

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
			$this->assignReceiverToGiver( $oNextGiver, $oNextReceiver );

			if ( $this->getIfGiverHasReachedAssignmentLimit( $oNextGiver ) ) {
				$this->removeGiverFromFutureAssignments( $oNextGiver );
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
	 * @param string $sGiver
	 * @param string $sReceiver
	 * @return $this
	 */
	protected function removeReceiverFromGiverPotentials( $sGiver, $sReceiver ) {
		$nKey = array_search( $sReceiver, $this->aBaselinePotentialMatches[ $sGiver ] );
		if ( $nKey !== false ) {
			unset( $this->aBaselinePotentialMatches[ $sGiver ][ $nKey ] );
		}
		return $this;
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
			$this->aFinalPeopleMatches = array();
			$this->aCountPresentsForReceivers = array();
			$this->aCountPresentsForGivers = array();

			$aExclusions = $this->getConfig()->getExclusionSets();

			$this->aEveryone = array();
			foreach ( $this->getConfig()->getPeople() as $sPerson ) {
				$this->aEveryone[ $sPerson ] = ( new Person( $sPerson ) );
			}

			foreach ( $this->aEveryone as $oPerson ) {
				$oPerson
					->setPotentialReceivers( $this->aEveryone )
					->processExclusions( $aExclusions );

				$this->aCountPresentsForReceivers[ $oPerson->getId() ] = 0;
				$this->aCountPresentsForGivers[ $oPerson->getId() ] = 0;

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
	 * @param Person $oReceiver
	 * @throws \Exception
	 */
	protected function assignReceiverToGiver( Person $oGiver, Person $oReceiver ) {
		$this->aCountPresentsForReceivers[ $oReceiver->getId() ]++;
		$this->aCountPresentsForGivers[ $oGiver->getId() ]++;
		$oGiver->setFinalReceiver( $oReceiver );
//		if ( !in_array( $oReceiver->getId(), $this->aFinalPeopleMatches[ $oGiver->getId() ] ) ) {
//		}
//		else {
//			throw new \Exception( 'Should not be possible to assign a receiver to a giver more than once!' );
//		}
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
	protected function removeGiverFromFutureAssignments( Person $oGiver ) {
		unset( $this->aWorkingGiversPool[ $oGiver->getId() ] );
		return $this;
	}

	/**
	 * @param Person $oReceiver
	 * @return bool
	 */
	protected function getIfReceiverHasReachedAssignmentLimit( Person $oReceiver ) {
		return ( $this->aCountPresentsForReceivers[ $oReceiver->getId() ] == $this->getConfig()->getNumberOfPresentsEach() );
	}

	/**
	 * @param Person $oReceiver
	 * @return $this
	 */
	protected function removeReceiverFromAllPotentials( Person $oReceiver ) {
		foreach( $this->getEveryone() as $sGiver => $oPerson ) {
			$oPerson->removePotentialReceiver( $oReceiver );
		}
		return $this;
	}

	/**
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