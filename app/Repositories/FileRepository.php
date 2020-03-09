<?php

namespace App\Repositories;

use App\File;
use App\Task;
use App\Repositories\Interfaces\FileRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use App\Exceptions\CreateFileErrorException;
use Exception;
use Illuminate\Support\Collection;

class FileRepository extends BaseRepository implements FileRepositoryInterface
{
    /**
     * FileRepository constructor.
     *
     * @param File $file
     */
    public function __construct(File $file)
    {
        parent::__construct($file);
        $this->model = $file;
    }

    /**
     * @param array $data
     *
     * @return File
     * @throws CreateFileErrorException
     */
    public function createFile(array $data): File
    {
        try {
            return $this->create($data);
        } catch (QueryException $e) {
            throw new CreateFileErrorException($e);
        }
    }

    /**
     * @param int $id
     *
     * @return File
     * @throws Exception
     */
    public function findFileById(int $id): File
    {
        return $this->findOneOrFail($id);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteFile(): bool
    {
        return $this->delete();
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     *
     * @return Collection
     */
    public function listFiles($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Collection
    {
        return $this->all($columns, $orderBy, $sortBy);
    }

    public function getFilesForEntity($entity)
    {

        return File::where('documentable_id', $entity->id)->where('documentable_type', get_class($entity))
                   ->orderBy('created_at', 'desc')->with('user')->get();
    }
}
