<?php

namespace Apto\Fun\SecretSanta\Components;

use Apto\Fun\SecretSanta\Components\Person;

class Printer {

	/**
	 * @param Person[] $aEveryone
	 * @return \stdClass
	 */
	public function run( $aEveryone ) {
		sort( $aEveryone, SORT_NATURAL );
		foreach ( $aEveryone as $sPersonId => $oPerson ) {
			echo sprintf( '<h3>%s</h3>', $oPerson->getId() );
			echo '<ul>';
			foreach ( $oPerson->getFinalReceivers() as $oReceiver ) {
				echo sprintf( '<li>%s</li>', $oReceiver->getId() );
			}
			echo '</ul>';
		}
	}
}