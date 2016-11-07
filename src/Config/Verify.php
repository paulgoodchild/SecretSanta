<?php

namespace Apto\Fun\SecretSanta\Config;

use Apto\Fun\SecretSanta\Components\Person;

class Verify {

	use ConfigConsumer;

	/**
	 * @return \stdClass
	 */
	public function run() {

		// ensure raw config items
		$this->config();
		// verify people
		$this->people();
		// verify exclusions
		$this->exclusions();
		// ensure presents > 0
		$this->presents();
	}

	protected function config() {
		$aConfig = $this->getConfig()->getRawConfig();
		foreach( [ 'rules', 'people' ] as $sKey ) {
			if ( empty( $aConfig[ $sKey ] ) ) {
				throw new \Exception( sprintf( 'Configuration Section "%s" is not provided', $sKey ) );
			}
		}

		foreach( [ 'required_fields', 'unique_key', 'presents_each' ] as $sKey ) {
			if ( empty( $aConfig[ 'rules' ][ $sKey ] ) ) {
				throw new \Exception( sprintf( 'Configuration Rules option "%s" is not provided', $sKey ) );
			}
		}
	}

	protected function presents() {
		$oConfig = $this->getConfig();
		$nNumberOfPresents = $oConfig->getNumberOfPresentsEach();
		if ( !is_numeric( $nNumberOfPresents ) ) {
			throw new \Exception( sprintf( 'Number of presents each "%s" is not a number', $nNumberOfPresents ) );
		}
		if ( (int)$nNumberOfPresents < 1 ) {
			throw new \Exception( sprintf( 'Number of presents each "%s" is less than 1', $nNumberOfPresents ) );
		}

		$nNumberOfPeople = count( $oConfig->getUniquePersonKeys() );
		if ( $nNumberOfPresents > $nNumberOfPeople ) {
			throw new \Exception( sprintf( 'Number of presents each (%s) exceeds the number of people (%s)', $nNumberOfPresents, $nNumberOfPeople ) );
		}
	}

	protected function people() {
		$oConfig = $this->getConfig();
		$aPeople = $oConfig->getPeople();
		if ( empty( $aPeople ) ) {
			throw new \Exception( 'No people have been specified' );
		}
		if ( !is_array( $aPeople ) ) {
			throw new \Exception( 'People data should be an array' );
		}

		$sUniqueKey = $oConfig->getUniquePersonKey();
		$sRequiredFields = $oConfig->getRequiredPeopleFields();
		foreach ( $aPeople as $nKey => $aPersonInfo ) {
			if ( empty( $aPersonInfo[ $sUniqueKey ] ) ) {
				throw new \Exception( sprintf( 'Person at position %s does not have the unique key specified', $nKey ) );
			}

			foreach( $sRequiredFields as $sField ) {
				if ( !isset( $aPersonInfo[ $sField ] ) ) {
					throw new \Exception( sprintf( 'Person at position %s does not have the required field "%s" specified', $nKey, $sField ) );
				}
			}
		}

		$aAllUniqueKeys = $oConfig->getUniquePersonKeys();
		if ( count( $aAllUniqueKeys ) != count( array_unique( $aAllUniqueKeys ) ) ) {
			throw new \Exception( sprintf( 'It appears that the unique keys (%s) are not unique for each person', $sUniqueKey ) );
		}
	}

	protected function exclusions() {
		$oConfig = $this->getConfig();
		$aAllUniqueKeys = $oConfig->getUniquePersonKeys();
		$aExclusionSets = $oConfig->getExclusionSets();

		foreach ( $aExclusionSets as $nKey => $aExclusionSet ) {
			if ( empty( $aExclusionSet ) || !is_array( $aExclusionSet ) || count( $aExclusionSet ) < 2 ) {
				throw new \Exception( sprintf( 'Exclusion set at position %s does not contain at least 2 members', $nKey ) );
			}

			$aDiff = array_diff( $aExclusionSet, $aAllUniqueKeys );
			if ( !empty( $aDiff ) ) {
				throw new \Exception(
					sprintf( 'Exclusion set at position %s contains at least 1 key that does not exist: "%s"',
						$nKey,
						var_export( $aDiff, true )
					)
				);
			}
		}
	}
}