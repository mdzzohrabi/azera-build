<?php
namespace Azera\Build\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Azera\Build\Project;
use Azera\Build\Exception;
use Azera\Build\DS;

class Projects extends Command {
	
	function configure() {

		$this
			->setName( 'projects' )
			->setDescription( 'List of projects' );

	}

	function execute( InputInterface $input , OutputInterface $output ) {

		$output->writeln( 'List of available projects : ' . "\n" );

		foreach ( $this->getApplication()->getProjects() as $project ) {
			$output->writeln( ' - ' . $project->getName() . ' v' . $project->getVersion() );
			$output->writeln( '   ' . $project->getDescription() . "\n" );
		}

	}

}
?>