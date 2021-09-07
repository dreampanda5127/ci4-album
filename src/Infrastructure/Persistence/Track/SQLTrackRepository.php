<?php

/**
 * This file is part of samsonasik/ci4-album.
 *
 * (c) 2020 Abdul Malik Ikhsan <samsonasik@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Album\Infrastructure\Persistence\Track;

use Album\Domain\Album\Album;
use Album\Domain\Track\Track;
use Album\Domain\Track\TrackNotFoundException;
use Album\Domain\Track\TrackRepository;
use Album\Infrastructure\Persistence\DMLPersistence;
use Album\Models\TrackModel;

final class SQLTrackRepository implements TrackRepository
{
    use DMLPersistence;

    /**
     * @var TrackModel
     */
    private $model;

    public function __construct(TrackModel $model)
    {
        $this->model = $model;
    }

    public function findPaginatedData(Album $album, string $keyword = ''): ?array
    {
        $this->model
            ->builder()
            ->where('album_id', $album->id);

        if ($keyword !== '') {
            $this->model
                ->builder()
                ->groupStart()
                ->like('title', $keyword)
                ->orLike('author', $keyword)
                ->groupEnd();
        }

        return $this->model->paginate(config('Album')->paginationPerPage);
    }

    public function findTrackOfId(int $id): Track
    {
        $track = $this->model->find($id);
        if (! $track instanceof Track) {
            throw TrackNotFoundException::forAlbumTrackDoesnotExistOfId($id);
        }

        return $track;
    }

    public function deleteOfId(int $id): bool
    {
        $this->model->delete($id);
        if ($this->model->db->affectedRows() === 0) {
            throw TrackNotFoundException::forAlbumTrackDoesnotExistOfId($id);
        }

        return true;
    }
}
