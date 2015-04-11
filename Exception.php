<?php
namespace Azera\Build;

class Exception extends \Exception {
	
	public static function projectNotFound( $name ) {
		
		return new static( sprintf( 'Project `%s` not found.' , $name ) );

	}

	public static function buildDirNotFound() {

		return new static( 'Build directory not found.' );

	}

	public static function dirNotFound( $dir ) {

		return new static( sprintf( 'Directory `%s` not found.' , $dir ) );

	}

	static function archiveCreationFail( $archive ) {

		return new static( sprintf( 'Cannot create `%s` archive file.' , $archive ) );

	}

}
?>