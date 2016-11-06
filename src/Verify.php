<?php

namespace Apto\Fun\SecretSanta;

class Verify {

	use ConfigConsumer;

	/**
	 * @param Person[] $aEveryone
	 * @return \stdClass
	 */
	public function run( $aEveryone ) {
		$oConfig = $this->getConfig();
		$oResult = new \stdClass();
		$oResult->success = true;
		$oResult->message = 'Success';
		$oResult->config = $oConfig;
		$oResult->final_matching = $aEveryone;

		$aPersonPresentCount = array();
		foreach ( $aEveryone as $sPerson => $oPerson ) {
			foreach ( $oPerson->getFinalReceivers() as $oReceiver ) {
				if ( !isset( $aPersonPresentCount[ $oReceiver->getId() ] ) ) {
					$aPersonPresentCount[ $oReceiver->getId() ] = 1;
				}
				else {
					$aPersonPresentCount[ $oReceiver->getId() ] += 1;
				}
			}
		}

		if ( empty( $aEveryone ) ) {
			$oResult->success = false;
			$oResult->message = 'The Final matches is empty';
		}

		if ( count( $oConfig->getPeople() ) != count( $aPersonPresentCount ) ) {
			$oResult->success = false;
			$oResult->message = sprintf( 'The total person present count (%s) does not equal that of the configuration', count( $aPersonPresentCount ) );
		}

		if ( $oResult->success ) {
			foreach ( $oConfig->getPeople() as $sPerson ) {
				if ( !isset( $aPersonPresentCount[$sPerson]) ) {
					$oResult->success = false;
					$oResult->message = sprintf( 'ERROR: USER %s does not have any presents assigned', $sPerson );
				}
			}
		}

		if ( $oResult->success ) {
			foreach ( $aPersonPresentCount as $sPerson => $nPresentCount ) {
				if ( $nPresentCount != $oConfig->getNumberOfPresentsEach() ) {
					$oResult->success = false;
					$oResult->message = sprintf( 'ERROR: USER %s does not have correct number of presents ', $sPerson );
				}
			}
		}

		if ( $oResult->success ) {
			$aExclusions = $oConfig->getExclusionSets();
			foreach( $aExclusions as $aExclusionSet ) {

				foreach ( $aEveryone as $sPerson => $oPerson ) {
					if ( in_array( $sPerson, $aExclusionSet ) ) {
						$aIntersect = array_intersect( $aExclusionSet, $oPerson->getFinalReceivers() );
					}
					if ( !empty( $aIntersect ) ) {
						$oResult->success = false;
						$oResult->message = sprintf( 'ERROR: USER %s has assignments within their exclusions', $sPerson );
						break(2);
					}
				}
			}
		}

		return $oResult;
	}
}