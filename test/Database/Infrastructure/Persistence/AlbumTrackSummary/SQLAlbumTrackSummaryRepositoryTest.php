<?php namespace AlbumTest\Database\Infrastructure\Persistence\Album;

use Album\Database\Seeds\AlbumSeeder;
use Album\Database\Seeds\TrackSeeder;
use CodeIgniter\Test\CIDatabaseTestCase;
use Config\Services;

class SQLAlbumTrackSummaryRepositoryTest extends CIDatabaseTestCase
{
	protected $basePath  = __DIR__ . '/../src/Database/';
	protected $namespace = 'Album';
	protected $seed      = [
		AlbumSeeder::class,
		TrackSeeder::class,
	];
	private $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = Services::albumTrackSummary();
	}

	public function testFindPaginatedSummaryTotalSongDataFoundInDB()
	{
		$albumtracksummary = $this->repository->findPaginatedSummaryTotalSongData();
		$this->assertNotEmpty($albumtracksummary);
	}
}
