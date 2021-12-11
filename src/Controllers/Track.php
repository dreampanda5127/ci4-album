<?php

/**
 * This file is part of samsonasik/ci4-album.
 *
 * (c) 2020 Abdul Malik Ikhsan <samsonasik@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Album\Controllers;

use Album\Domain\Album\AlbumRepository;
use Album\Domain\Exception\RecordNotFoundException;
use Album\Domain\Track\TrackRepository;
use Album\Models\TrackModel;
use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Services;

final class Track extends BaseController
{
    /**
     * @var IncomingRequest
     */
    protected $request;

    /**
     * @var AlbumRepository
     */
    private $albumRepository;

    /**
     * @var TrackRepository
     */
    private $trackRepository;

    /**
     * @var string
     */
    private const KEYWORD = 'keyword';

    /**
     * @var string
     */
    private const ALBUM = 'album';

    /**
     * @var string
     */
    private const STATUS = 'status';

    /**
     * @var string
     */
    private const TRACK_INDEX = 'track-index';

    /**
     * @var string
     */
    private const ERRORS = 'errors';

    public function __construct()
    {
        $this->albumRepository = Services::albumRepository();
        $this->trackRepository = Services::trackRepository();
    }

    public function index(int $albumId): string
    {
        $data = [];
        try {
            $album = $this->albumRepository->findAlbumOfId($albumId);
        } catch (RecordNotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        $data[self::KEYWORD] = $this->request->getGet(self::KEYWORD) ?? '';
        $data[self::ALBUM]   = $album;
        $data['tracks']      = $this->trackRepository->findPaginatedData($album, $data[self::KEYWORD]);
        $data['pager']       = model(TrackModel::class)->pager;

        return view('Album\Views\track\index', $data);
    }

    /**
     * @return RedirectResponse|string
     */
    public function add(int $albumId)
    {
        try {
            $album = $this->albumRepository->findAlbumOfId($albumId);
        } catch (RecordNotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            if ($this->trackRepository->save($data)) {
                session()->setFlashdata(self::STATUS, 'New album track has been added');

                return redirect()->route(self::TRACK_INDEX, [$albumId]);
            }

            session()->setFlashdata(self::ERRORS, model(TrackModel::class)->errors());

            return redirect()->withInput()->back();
        }

        return view('Album\Views\track\add', [
            self::ALBUM  => $album,
            self::ERRORS => session()->getFlashData(self::ERRORS),
        ]);
    }

    /**
     * @return RedirectResponse|string
     */
    public function edit(int $albumId, int $trackId)
    {
        try {
            $album = $this->albumRepository->findAlbumOfId($albumId);
            $track = $this->trackRepository->findTrackOfId($trackId);
        } catch (RecordNotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            if ($this->trackRepository->save($data)) {
                session()->setFlashdata(self::STATUS, 'Album track has been updated');

                return redirect()->route(self::TRACK_INDEX, [$albumId]);
            }

            session()->setFlashdata(self::ERRORS, model(TrackModel::class)->errors());

            return redirect()->withInput()->back();
        }

        return view('Album\Views\track\edit', [
            self::ALBUM  => $album,
            'track'      => $track,
            self::ERRORS => session()->getFlashData(self::ERRORS),
        ]);
    }

    public function delete(int $albumId, int $trackId): RedirectResponse
    {
        try {
            $this->albumRepository->findAlbumOfId($albumId);
            $this->trackRepository->deleteOfId($trackId);
        } catch (RecordNotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        session()->setFlashdata(self::STATUS, 'Album track has been deleted');

        return redirect()->route(self::TRACK_INDEX, [$albumId]);
    }
}
