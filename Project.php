<?php
namespace Azera\Build;

class Project implements \ArrayAccess {
	
	private $conf = array();
	private $baseDir;
	private $ignoredFiles = array();
	private $files = array();

	function __construct( array $conf , $baseDir ) {

		$this->conf = $conf;
		$this->baseDir = $conf['baseDirectory'] ?: $baseDir;

	}

	function getFiles() {

		return $this->files ?: $this->files = array_unique( $this->getFilesRecursive() );

	}

	protected function getFilesRecursive( array $root = null , $base = null , $fullPath = false ) {


		$base = $base !== null ? $base : $this->baseDir;
		
		$root = $root !== null ? $root : ( $this['files']['include'] ?: null );

		if ( substr( $base , -1 , 1 ) == '/' ) $base = substr( $base , 0 , strlen( $base ) - 1 );

		if ( !file_exists( $base ) )
			throw Exception::dirNotFound( $base );

		if ( $root === null ) {
			$root = glob( $base . DS . '*' );
			$fullPath = true;
		}

		$checkIgnore = function ( $name ) {
			$name = substr( $name , strlen( $this->baseDir ) + 1 );
			foreach ( $this->getIgnoredFiles() as $ignore )
					if ( preg_match( $ignore , $name ) ) return true;
			return false;
		};

		$files = array();

		foreach ( $root as $k => $v )
		{

			if ( is_string( $k ) ) {
				
				$files = array_merge( $files , $this->getFilesRecursive( (array)$v , $base . DS . $k ) );

			} else {

				if ( is_array( $v ) )
				{
					$files = array_merge( $files , $this->getFilesRecursive( $v , $base ) );
				}
				else
				{

					$v = !$fullPath ? $base . DS . $v : $v;

					if ( $checkIgnore( $v ) ) continue;

					if ( is_file( $v ) )
					{
						$files[] = $v;
					}
					else
					{
						$files = array_merge( $files , $this->getFilesRecursive( glob( $v . DS . '*' ) , $v , true ) );
					}
				}

			}


		}

		return $files;

	}

	function getIgnoredFiles() {

		if ( $this->ignoredFiles ) return $this->ignoredFiles;

		foreach ( (array)$this['files']['ignore'] as $ignore ) {

			if ( $ignore[0] != '/' )
				$ignore = '*/' . $ignore;
			
			$ignore = '/' . strtr( $ignore , [
					'/'	=> '(\/|\\\\)',
					'\\'	=> '(\\\\|\/)',
					'*'		=> '.*?',
					'.'		=> '\.',
					'+'		=> '.+'
				] ) . '$/A';

			$this->ignoredFiles[] = $ignore;

		}

		return $this->ignoredFiles;
	}

	function getDescription() {
		return $this['description'];
	}

	function getBase() {
		return $this->baseDir;
	}

	function getName() {
		return $this['name'];
	}

	function getVersion() {
		return $this['version'];
	}

	function offsetGet( $offset ) {
		return $this->conf[ $offset ];
	}

	function offsetSet( $offset , $value ) {
		$this->conf[ $offset ] = $value;
	}

	function offsetUnset( $offset ) {
		unset( $this->conf[ $offset ] );
	}

	function offsetExists( $offset ) {
		return isset( $this->conf[ $offset ] );
	}

}
?>