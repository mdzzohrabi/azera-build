<?php
namespace Azera\Build;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

class Application extends ConsoleApplication {

	private $input;
	private $projects;

	function __construct() {

		parent::__construct('Azera Build' , '1.0');
		
		$this->getDefinition()->addOptions([
				new InputOption( 'base' , null , InputOption::VALUE_OPTIONAL , 'Project base directory' , getcwd() )
			]);

	}

	function run( InputInterface $input = null , OutputInterface $output = null ) {
		
		$this->input = $input = new ArgvInput;
		$output = new ConsoleOutput;

		parent::run( $input , $output );

	}

	function getBase() {

		return $this->input->hasOption('base') ? $this->input->getOption( 'base' ) : getcwd();
	
	}

	function getBuildPath() {

		return $this->getBase() . DS . BUILD_DIR;

	}

	function getReleasePath() {

		return $this->getBuildPath() . DS . 'release';

	}

	function getProjects() {

		if ( $this->projects ) return $this->projects;

		$dir = $this->getBuildPath();

		if ( !file_exists( $dir ) )
			throw Exception::buildDirNotFound();

		$projects = glob( $dir . '/*.yml' );

		foreach ( $projects as &$project ) {
			$project = new Project( Yaml::parse( file_get_contents( $project ) ) , $this->getBase() );
		}

		return $this->projects = $projects;

	}

	function getProject( $name , $version = null ) {

		foreach ( $this->getProjects() as $project )
			if ( $project->getName() == $name && ( $version == null || $project->getVersion() == $version ) )
				return $project;

		return false;

	}

}
?>