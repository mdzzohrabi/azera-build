<?php
namespace Azera\Build\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Azera\Build\Exception;
use ZipArchive;

class Build extends Command {
	
	function configure() {

		$this
			->setName( 'compile' )
			->setDescription( 'Build a project' )
			->addArgument(
					'project',
					InputArgument::REQUIRED,
					'Project ?'
				)
			->addArgument(
					'version',
					InputArgument::OPTIONAL,
					'Version ?'
				)
			->addOption(
					'file',
					null,
					InputOption::VALUE_OPTIONAL,
					'Archive name ?',
					false
				)
			->addOption(
					'root',
					null,
					InputOption::VALUE_OPTIONAL,
					'Files root directory ?',
					false
				);

	}

	function execute( InputInterface $input , OutputInterface $output ) {

		$buildTime = microtime( true );
		$version = $input->hasArgument( 'version' ) ? $input->getArgument( 'version' ) : null;
		$rootDir = $input->hasOption( 'root' ) && $input->getOption( 'root' ) ? $input->getOption( 'root' ) : null;

		$project = $this->getApplication()->getProject(
				$input->getArgument( 'project' ),
				$version
			);

		if ( !$project )
			throw Exception::projectNotFound( $input->getArgument( 'project' ) );

		$archiveName = $input->hasOption( 'file' ) && $input->getOption( 'file' ) ? $input->getOption( 'file' ) : $project->getName()  . '-' . $project->getVersion() . '.zip';

		$archiveName = strtr( $archiveName , [ '/' => '_' , '\\' => '_' ] );

		if ( !file_exists( $this->getApplication()->getReleasePath() ) )
			mkdir( $this->getApplication()->getReleasePath() , true );

		$archivePath = $this->getApplication()->getReleasePath() . '/' . $archiveName;

		$prog = new ProgressBar( $output , 10 );

		$output->writeln( '<info>Build Project</info>' . "\n" );

		$output->writeln( 'Project name : ' . $project->getName() );

		$output->writeln( 'Project version : ' . $project->getVersion() );

		$files = $project->getFiles();

		if ( $rootDir ) {
			$temp = [];
			foreach ( $files as $file )
				if ( str_replace( '\\' , '/' , substr( $file , strlen( $project->getBase() ) + 1 , strlen( $rootDir ) ) ) == $rootDir )
					$temp[] = $file;
			$files = $temp;
		}

		$filesCount = count( $files );

		$output->writeln( 'Project files : ' . $filesCount );

		$prog->setMessage( 'Add project files to archive ...' );

		$zip = new ZipArchive;

		if ( $zip->open( $archivePath , ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
			throw Exception::archiveCreationFail( $archivePath );
		}

		$prog->start();

		for ( $i = 0 ; $i < $filesCount ; $i++ ) {

			$path = $files[ $i ];

			$zip->addFile( $path , str_replace( '\\' , '/', substr( $path , strlen( $project->getBase() ) + 1 ) ) );

			if ( $i % ( $filesCount / 10 ) == 0 ) $prog->advance();

		}

		$prog->setMessage( 'Copies completed.' );

		$prog->finish();

		$output->writeln( "\n" . 'Make archive file...' );

		if ( $zip->close() === false )
			throw Exception::archiveCreationFail( $archivePath );

		$output->writeln( 'Compiled file : ' . $archivePath );

		$buildTime = ( microtime( true ) - $buildTime ) / 60 ;

		$output->writeln( sprintf( 'Build Complete in %.2f sec.' , $buildTime ) );


	}

}
?>