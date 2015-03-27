<?php

include( 'identicon.class.php' );

$image = new Identicon();
$image->setSize( 300 );
$image->rotator( TRUE );
$image->filterize( TRUE );
//$image->setImage( 'galaxy.jpg' );
$image->useImagePool( './imagepool' );
$image->hashBase( 'xxx'. microtime() );

$image->display();
//$image->generate( true );

/* * /
for( $i=0; $i<20; $i++ )
{
	$image->setOutputPath( 'out' );
	$image->setOutputFilename( $i.'.png' );
	$image->hashBase( 'xxx'.microtime() );
	$image->generate();
}
/* */