<?php

require( __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

$sPathToConfig = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.yml';
$aConfig = ( new \Symfony\Component\Yaml\Yaml() )->parse( file_get_contents( $sPathToConfig ) );
$oConfig = new \Apto\Fun\SecretSanta\Config( $aConfig );

$aPeople = ( new \Apto\Fun\SecretSanta\Secret() )
	->setConfig( $oConfig )
	->run()
	->getEveryone();

$oResult = ( new \Apto\Fun\SecretSanta\Verify() )
	->setConfig( $oConfig )
	->run( $aPeople );

if ( $oResult->success && $oConfig->getIfPrint() ) {
	( new \Apto\Fun\SecretSanta\Printer() )->run( $aPeople );
}
else {
	echo '<h1>FAILED</h1>';
	echo sprintf( '<h3>%s</h3>', $oResult->message );
}