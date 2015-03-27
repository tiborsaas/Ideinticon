<?php

/*******************************************************************************
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Description:
 *  ---------------------------------------------------------------------------
 *  Lightweight PHP implementation of Don Park's original identicon code for 
 *  isual representation of MD5 hash values. The program uses the standard 
 *  HP GD library for image processing.
 *
 *  This class doesn't do more than it should be. You should implement 
 *  You should implement caching for example
 *
 *	If you setup the useImagePool() method, then you can use up to 65535 images 
 *
 *  Usage:
 *  ---------------------------------------------------------------------------
 *  $icon = new Identicon();
 *  $icon->setSize( 300 );
 *  $icon->rotator( TRUE ); // more variation to the identicon by rotating it
 *  $icon->setImage( 'niceimage.jpg' ); // also supports png
 *  $icon->hashBase( 'some text whatever' );
 *
 *  $icon->generate( TRUE ); // this will generate the identicon and save it
 *
 *  // or insted of generating, just display it on the output directly
 *  $icon->display();
 *
 *  Credits:
 *  ---------------------------------------------------------------------------
 *  Original code taken from: http://sourceforge.net/projects/identicons/
 *  and written by Bong Cosca
 *
 *	Refactored to OOP version: 
 *
 *	Tibor Szász
 *	https://github.com/kowdermeister
 *	http://tibor.szasz.hu
 *	http://twitter.com/kowww
 *
 *  Added features
 *   - Image support
 *   - Save the identicon
 *   - A lot of setter methods
 *
 *******************************************************************************/

class Identicon
{
	private $iconSize;
	private $spriteSize;
	private $imagePath;
	private $outputPath;
	private $outputFileName;
	private $toRotate;
	private $filterize;
	private $hash;
	private $imagePoolPath;

	/**
	 *	Setup some default values
	 */
	public function __construct()
	{
		/* size of each sprite */
		$this->spriteSize = 128;

		/* size of the generated identicon */
		$this->iconSize = 256;

		/* set the default output path to the current directory */
		$this->outputPath = './';
	}

	/**
	 *	Generate sprite for corners and sides 
	 */
	private function getSprite( $shape, $R, $G, $B, $rotation ) 
	{
		if( !$this->imagePath )
		{
			die( 'A path to the source image must be provied!' );
		}

		/**
		 *	Tweak source image, crop it to a square
		 */
		list( $origWidth, $origHeight, $type ) = getimagesize( $this->imagePath );

		switch( $type )
		{
			case 2:
				$sourceImage = imagecreatefromjpeg( $this->imagePath );
			break;

			case 1:
				$sourceImage = imagecreatefromgif( $this->imagePath );
			break;

			case 3:
				$sourceImage = imagecreatefrompng( $this->imagePath );
			break;

			default: 
				die( var_dump( $type ) );
			break;
		}

		$croppedImage = imagecreatetruecolor( $this->spriteSize, $this->spriteSize );

		/* Resize the input image to the sprite site */
		if( $origWidth / $origHeight > 1 )
		{
			// Landscape mode
			$sourceX = ( $origWidth - $origHeight ) / 2;
			$sourceY = 0;
			$origWidth = $origHeight; // The square is determined by the height
		}
		else
		{
			// Portrait mode
			$sourceX = 0;
			$sourceY = ( $origHeight - $origWidth ) / 2;;
			$origHeight = $origWidth; // The square is determined by the width
		}

		imagecopyresized( $croppedImage, $sourceImage, 0, 0, $sourceX, $sourceY, $this->spriteSize, $this->spriteSize, $origWidth, $origHeight );

		//imagejpeg( $croppedImage, 'debug.jpg' );

		/**
		 *	Start generation the sprite
		 */
		$sprite = imagecreatetruecolor( $this->spriteSize, $this->spriteSize );

		if( function_exists( 'imageantialias' ) )
		{
			imageantialias( $sprite, TRUE );			
		}

		$fg = imagecolorallocate( $sprite, $R, $G, $B );
		$bg = imagecolorallocate( $sprite, 255, 255, 255 );

		imagefilledrectangle( $sprite, 0, 0, $this->spriteSize, $this->spriteSize, $bg );

		// Copy opened image to the square

		switch($shape) 
		{
			case 0: // triangle
				$shape=array(
					0.5,1,
					1,0,
					1,1
				);
				break;
			case 1: // parallelogram
				$shape=array(
					0.5,0,
					1,0,
					0.5,1,
					0,1
				);
				break;
			case 2: // mouse ears
				$shape=array(
					0.5,0,
					1,0,
					1,1,
					0.5,1,
					1,0.5
				);
				break;
			case 3: // ribbon
				$shape=array(
					0,0.5,
					0.5,0,
					1,0.5,
					0.5,1,
					0.5,0.5
				);
				break;
			case 4: // sails
				$shape=array(
					0,0.5,
					1,0,
					1,1,
					0,1,
					1,0.5
				);
				break;
			case 5: // fins
				$shape=array(
					1,0,
					1,1,
					0.5,1,
					1,0.5,
					0.5,0.5
				);
				break;
			case 6: // beak
				$shape=array(
					0,0,
					1,0,
					1,0.5,
					0,0,
					0.5,1,
					0,1
				);
				break;
			case 7: // chevron
				$shape=array(
					0,0,
					0.5,0,
					1,0.5,
					0.5,1,
					0,1,
					0.5,0.5
				);
				break;
			case 8: // fish
				$shape=array(
					0.5,0,
					0.5,0.5,
					1,0.5,
					1,1,
					0.5,1,
					0.5,0.5,
					0,0.5
				);
				break;
			case 9: // kite
				$shape=array(
					0,0,
					1,0,
					0.5,0.5,
					1,0.5,
					0.5,1,
					0.5,0.5,
					0,1
				);
				break;
			case 10: // trough
				$shape=array(
					0,0.5,
					0.5,1,
					1,0.5,
					0.5,0,
					1,0,
					1,1,
					0,1
				);
				break;
			case 11: // rays
				$shape=array(
					0.5,0,
					1,0,
					1,1,
					0.5,1,
					1,0.75,
					0.5,0.5,
					1,0.25
				);
				break;
			case 12: // double rhombus
				$shape=array(
					0,0.5,
					0.5,0,
					0.5,0.5,
					1,0,
					1,0.5,
					0.5,1,
					0.5,0.5,
					0,1
				);
				break;
			case 13: // crown
				$shape=array(
					0,0,
					1,0,
					1,1,
					0,1,
					1,0.5,
					0.5,0.25,
					0.5,0.75,
					0,0.5,
					0.5,0.25
				);
				break;
			case 14: // radioactive
				$shape=array(
					0,0.5,
					0.5,0.5,
					0.5,0,
					1,0,
					0.5,0.5,
					1,0.5,
					0.5,1,
					0.5,0.5,
					0,1
				);
				break;
			default: // tiles
				$shape=array(
					0,0,
					1,0,
					0.5,0.5,
					0.5,0,
					0,0.5,
					1,0.5,
					0.5,1,
					0.5,0.5,
					0,1
				);
				break;
		}

		/* apply ratios */

		for ( $i=0; $i < count ( $shape ); $i++ )
		{
			$shape[ $i ] = $shape[ $i ] * $this->spriteSize;
		}

		imagefilledpolygon( $sprite, $shape, count($shape) / 2, $fg );

		/* rotate the sprite */
		for ( $i = 0; $i < $rotation; $i++ )
		{
			$sprite = imagerotate( $sprite, 90, $bg );
			
		}

		imagealphablending( $sprite, true );
		imagecolortransparent( $sprite, $fg );

		/* Merge the sprite with the source image */
		imagecopymerge( $croppedImage, $sprite, 0, 0, 0, 0, $this->spriteSize, $this->spriteSize, 100 );

		return $croppedImage;
	}


	/**
	 *	Generate sprite for center block 
	 */
	function getCenter( $shape, $fR, $fG, $fB, $bR, $bG, $bB, $usebg ) 
	{
		$sprite = imagecreatetruecolor( $this->spriteSize, $this->spriteSize );

		if( function_exists( 'imageantialias' ) )
		{
			imageantialias( $sprite, TRUE );
		}
		$fg = imagecolorallocate( $sprite, $fR, $fG, $fB );

		/* make sure there's enough contrast before we use background color of side sprite */
		if ( $usebg > 0 && ( abs( $fR-$bR ) > 127 || abs( $fG-$bG )>127 || abs( $fB-$bB ) > 127 ) )
		{
			$bg = imagecolorallocate( $sprite, $bR, $bG, $bB );
		}
		else
		{
			$bg = imagecolorallocate( $sprite, 255, 255, 255 );			
		}

		imagefilledrectangle( $sprite, 0, 0, $this->spriteSize, $this->spriteSize, $bg );

		switch( $shape ) 
		{
			case 0: // empty
				$shape=array();
				break;
			case 1: // fill
				$shape=array(
					0,0,
					1,0,
					1,1,
					0,1
				);
				break;
			case 2: // diamond
				$shape=array(
					0.5,0,
					1,0.5,
					0.5,1,
					0,0.5
				);
				break;
			case 3: // reverse diamond
				$shape=array(
					0,0,
					1,0,
					1,1,
					0,1,
					0,0.5,
					0.5,1,
					1,0.5,
					0.5,0,
					0,0.5
				);
				break;
			case 4: // cross
				$shape=array(
					0.25,0,
					0.75,0,
					0.5,0.5,
					1,0.25,
					1,0.75,
					0.5,0.5,
					0.75,1,
					0.25,1,
					0.5,0.5,
					0,0.75,
					0,0.25,
					0.5,0.5
				);
				break;
			case 5: // morning star
				$shape=array(
					0,0,
					0.5,0.25,
					1,0,
					0.75,0.5,
					1,1,
					0.5,0.75,
					0,1,
					0.25,0.5
				);
				break;
			case 6: // small square
				$shape=array(
					0.33,0.33,
					0.67,0.33,
					0.67,0.67,
					0.33,0.67
				);
				break;
			case 7: // checkerboard
				$shape=array(
					0,0,
					0.33,0,
					0.33,0.33,
					0.66,0.33,
					0.67,0,
					1,0,
					1,0.33,
					0.67,0.33,
					0.67,0.67,
					1,0.67,
					1,1,
					0.67,1,
					0.67,0.67,
					0.33,0.67,
					0.33,1,
					0,1,
					0,0.67,
					0.33,0.67,
					0.33,0.33,
					0,0.33
				);
				break;
		}

		/* apply ratios */

		for ( $i=0; $i < count( $shape ); $i++ )
		{
			$shape[ $i ] = $shape[ $i ] * $this->spriteSize;
		}

		if ( count( $shape ) > 0 )
		{
			imagefilledpolygon( $sprite, $shape, count($shape) / 2, $fg );
		}
		return $sprite;
	}


	/**
	 *	Pick an image from the image pool
	 *	@param (int)index and index determined by the hash
	 */
	private function pickImage( $index )
	{
		$images = array();

		if ( $handle = opendir( $this->imagePoolPath ) ) 
		{
			while ( FALSE !== ( $entry = readdir( $handle ) ) ) 
			{
				if ( $entry != "." && $entry != ".." ) 
				{
					$images[] = $entry;
				}
			}
			closedir( $handle );
		}

		return $images[ $index%count( $images ) ];
	}


	/**
	 *	Creates an MD5 hash for the generation
	 */
	public function hashBase( $text )
	{
		$this->hash = md5( $text );
	}


	/**
	 *	Setup the hash
	 */
	public function setHash( $hashStr )
	{
		$this->hash = $hashStr;
	}


	/**
	 *	Setup the generated identicon's size in pixels
	 */
	public function setSize( $size )
	{
		$this->iconSize = $size;
	}


	/**
	 *	Set the path and filename to the image which should be used for the generated identicon
	 */
	public function setImage( $pathToImage )
	{
		if( !file_exists( $pathToImage ) )
		{
			die( 'Sorry, the "' . $pathToImage . '" image doesn\'t exists :(' );
		}
		$this->imagePath = $pathToImage;
	}


	/**
	 *	Use a bunch of images to generate the images
	 */
	public function useImagePool( $pathToPool )
	{
		if( !file_exists( $pathToPool ) )
		{
			die( 'Sorry, the "' . $pathToPool . '" directory doesn\'t exists :(' );
		}
		$this->imagePoolPath = $pathToPool;
	}



	/**
	 *	Set the path to the image which should be used for the generated identicon
	 */
	public function setOutputPath( $path )
	{
		if( !is_writable( $path ) )
		{
			die( 'Sorry, the path "' . $path . '" is not writable :(' );
		}
		$this->outputPath = $path;
	}


	/**
	 *	Set the path to the image which should be used for the generated identicon
	 */
	public function setOutputFilename( $fileName )
	{
		$this->outputFileName = $fileName;
	}


	/**
	 *	Should the generated image be filtered?
	 *	@param (bool)state
	 */
	public function filterize( $state )
	{
		$this->filterize = $state;
	}


	/**
	 * 	Setup if the generated image should be rotated
	 *	The rotation angle is based on the hash too
	 *	@param (bool)state
	 */
	public function rotator( $state )
	{
		$this->toRotate = $state;
	}


	/**
	 *	Display the image on the output
	 */
	public function display()
	{
		header( 'Content-Type: image/png' );
		imagepng( $this->generate( TRUE ) );
	}


	/**
	 *	This method generates the final image
	 *	@param (bool)returnImageResource determines if the generated image should be saved or returned
	 */
	public function generate( $returnImageResource = FALSE )
	{
		/* parse hash string */
		$csh = hexdec ( substr( $this->hash, 0, 1 ) ); // corner sprite shape
		$ssh = hexdec ( substr( $this->hash, 1, 1 ) ); // side sprite shape
		$xsh = hexdec ( substr( $this->hash, 2, 1 ) )&7; // center sprite shape

		$cro = hexdec ( substr( $this->hash, 3, 1 ) )&3; // corner sprite rotation
		$sro = hexdec ( substr( $this->hash, 4, 1 ) )&3; // side sprite rotation
		$xbg = hexdec ( substr( $this->hash, 5, 1 ) )%2; // center sprite background

		/* corner sprite foreground color */
		$cfr = hexdec ( substr( $this->hash, 6, 2 ) );
		$cfg = hexdec ( substr( $this->hash, 8, 2 ) );
		$cfb = hexdec ( substr( $this->hash, 10, 2 ) );

		/* side sprite foreground color */
		$sfr = hexdec( substr( $this->hash, 12, 2 ) );
		$sfg = hexdec( substr( $this->hash, 14, 2 ) );
		$sfb = hexdec( substr( $this->hash, 16, 2 ) );

		/* final angle of rotation */
		$angle = hexdec( substr( $this->hash, 18, 2 ) );

		/* filter colors */
		$filt_r = hexdec( substr( $this->hash, 19, 2 ) ) / 2;
		$filt_g = hexdec( substr( $this->hash, 20, 2 ) ) / 2;
		$filt_b = hexdec( substr( $this->hash, 21, 2 ) ) / 2;

		/* filter method */
		$filterType = hexdec( substr( $this->hash, 22, 1 ) )%4; // pick one of the 4 method

		/* pick an image from the pool. */
		if( $this->imagePoolPath )
		{
			$imageIndex = hexdec( substr( $this->hash, 23, 4 ) );
			$this->setImage( $this->imagePoolPath . '/' . $this->pickImage( $imageIndex ) );
		}

		/* end of hash parsing, maybe it should be moved in a method? */

		/* start with blank 3x3 identicon */
		$identicon = imagecreatetruecolor( $this->spriteSize * 3, $this->spriteSize * 3 );

		if( function_exists( 'imageantialias' ) )
		{
			imageantialias( $identicon, TRUE );
		}
		/* assign white as background */
		$bg = imagecolorallocate( $identicon, 255, 255, 255 );
		imagefilledrectangle( $identicon, 0, 0, $this->spriteSize, $this->spriteSize, $bg );

		/* generate corner sprites */
		$corner = $this->getSprite( $csh, $cfr, $cfg, $cfb, $cro );
		imagecopy( $identicon, $corner, 0, 0, 0, 0, $this->spriteSize, $this->spriteSize );

		$corner = imagerotate( $corner, 90, $bg );
		imagecopy( $identicon, $corner, 0, $this->spriteSize * 2, 0, 0, $this->spriteSize, $this->spriteSize );

		$corner = imagerotate( $corner, 90, $bg );
		imagecopy( $identicon, $corner, $this->spriteSize * 2, $this->spriteSize * 2, 0, 0, $this->spriteSize, $this->spriteSize );

		$corner = imagerotate( $corner, 90, $bg );
		imagecopy( $identicon, $corner, $this->spriteSize * 2, 0, 0, 0, $this->spriteSize, $this->spriteSize );

		/* generate side sprites */
		$side = $this->getSprite( $ssh, $sfr, $sfg, $sfb, $sro );
		imagecopy( $identicon, $side, $this->spriteSize, 0, 0, 0, $this->spriteSize, $this->spriteSize );

		$side = imagerotate( $side, 90, $bg );
		imagecopy( $identicon, $side, 0, $this->spriteSize, 0, 0, $this->spriteSize, $this->spriteSize );

		$side = imagerotate( $side, 90, $bg );
		imagecopy( $identicon, $side, $this->spriteSize, $this->spriteSize * 2, 0, 0, $this->spriteSize, $this->spriteSize );

		$side = imagerotate( $side, 90, $bg );
		imagecopy( $identicon, $side, $this->spriteSize * 2, $this->spriteSize, 0, 0, $this->spriteSize, $this->spriteSize );

		/* generate center sprite */
		$center = $this->getCenter( $xsh, $cfr, $cfg, $cfb, $sfr, $sfg, $sfb, $xbg );
		imagecopy( $identicon, $center, $this->spriteSize, $this->spriteSize, 0, 0, $this->spriteSize, $this->spriteSize );

		if( $this->toRotate )
		{
			$identicon = imagerotate( $identicon, $angle, $bg );			
		}

		/* create blank image according to specified dimensions */
		$resized = imagecreatetruecolor( $this->iconSize, $this->iconSize );

		if( function_exists( 'imageantialias' ) )
		{
			imageantialias( $resized, TRUE );
		}
		/* assign white as background */
		$bg = imagecolorallocate( $resized, 255, 255, 255 );
		imagefilledrectangle( $resized, 0, 0, $this->iconSize, $this->iconSize, $bg );

		/* resize ide nticon according to specification */
		imagecopyresampled(	$resized, 
							$identicon, 
							0, 
							0, 
							( imagesx ( $identicon ) - $this->spriteSize * 3 ) / 2,
							( imagesx ( $identicon ) - $this->spriteSize * 3 ) / 2,
							$this->iconSize,
							$this->iconSize,
							$this->spriteSize * 3,
							$this->spriteSize * 3
		);

		if( $this->filterize )
		{
			switch( $filterType )
			{
				case 0:
					imagefilter( $resized, IMG_FILTER_COLORIZE, $filt_r, $filt_g, $filt_b );
				break;

				case 1:
					imagefilter( $resized, IMG_FILTER_NEGATE );
				break;

				case 2:
					imagefilter( $resized, IMG_FILTER_GRAYSCALE );
				break;

				case 3:
					$version = explode( '.', PHP_VERSION );

					if( (int)$version[0] >= 5 && (int)$version[1] >= 3 )
					{
						imagefilter( $resized, IMG_FILTER_PIXELATE, $this->iconSize / 10, TRUE );
					}
				break;
			}
		}

		/**
		 *	Send back the identicon image result or save it to disk
		 */
		if( $returnImageResource )
		{
			return $resized;
		}
		else
		{
			$fileName = ( $this->outputFileName ) ? $this->outputFileName : $this->hash . '.png';
			$this->outputPath = ( substr( $this->outputPath, -1 ) == '/' ) ? $this->outputPath : $this->outputPath . '/';

			imagepng( $resized, $this->outputPath . $fileName, 7 );
		}
	}
}
