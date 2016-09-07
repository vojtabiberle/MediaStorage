<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Model;

use jasir\FileHelpers\File;
use Nette\Database\Table\Selection;
use vojtabiberle\MediaStorage\Bridges\IDatabaseStorage;
use vojtabiberle\MediaStorage\Exceptions\DatabaseStorageException;
use vojtabiberle\MediaStorage\FileFilter;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\IFileFilter;

class MediaStorage implements IDatabaseStorage
{
    const MEDIA_STORAGE_TABLE = 'media_storage';
    const MEDIA_USAGE_TABLE = 'media_usage';

    /** @var \Nette\Database\Context */
    protected $context;

    private $as_pairs = false;
    private $pairs = ['id', 'name'];

    /** @var IFileFilter */
    private $currentFilter = null;

    public function __construct(\Nette\Database\Context $context)
    {
        $this->context = $context;
    }

    public function store(IFile $file)
    {
        if (is_null($file->getUID())) {
            $file->generateUID();
        }

        $data = [
            'id' => $file->getUID(),
            'name' => $file->getName(),
            'full_path' => $file->getFullPath(),
            'size' => $file->getSize(),
            'content_type' => $file->getContentType(),
            'is_image' => $file->isImage(),
        ];
        return $this->context->table('media_storage')->insert($data);
    }

    public function find(IFileFilter $filter)
    {
        $this->currentFilter = $filter;

        $filterType = $filter->getFilterType();
        $filterValue = $filter->getFilterValue();

        $pairs = $filter->getPairs();
        if (is_array($pairs)) {
            $this->as_pairs = true;
            $this->pairs = $pairs;
        }

        switch ($filterType) {
            case FileFilter::FILTER_FIND_ALL:
                return $this->findAll();
            case FileFilter::FILTER_FIND_BY_NAME:
                return $this->findByName($filterValue);
            case FileFilter::FILTER_FIND_CONTENT_TYPE:
                return $this->findByContentType($filterValue);
            case FileFilter::FILTER_FIND_IMAGES:
                return $this->findImages();
            case FileFilter::FILTER_FIND_NOT_IMAGES:
                return $this->findNotImages();
            case FileFilter::FILTER_FIND_NAMESPACE:
                return $this->findByNamespace($filterValue);
            case FileFilter::FILTER_GET_ID:
                return $this->getById($filterValue);
            case FileFilter::FILTER_GET_NAME:
                return $this->getByName($filterValue);
            case FileFilter::FILTER_GET_NAMESPACE:
                return $this->getByNamespace($filterValue);
            case FileFilter::FILTER_CUSTOM_WHERE:
                return $this->findByCustomWhere($filterValue);
            case FileFilter::FILTER_ORDER_DESC:
                return $this->findByOrderDESC();
            default:
                throw new DatabaseStorageException('Unsupported filter type: '. $filterType);
        }
    }

    public function findAll()
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE);
        return $this->fetchAll($selection);
    }

    public function findByName($name)
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('name LIKE ?', $name);

        return $this->fetchAll($selection);
    }

    public function getByName($name)
    {
        $selection =  $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('name LIKE ?', $name);

        return $this->fetch($selection);
    }

    public function findImages()
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('is_image = 1');

        return $this->fetchAll($selection);
    }

    public function findByOrderDESC()
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->order('id DESC')
            ->limit('50');

        return $this->fetchAll($selection);
    }

    public function findNotImages()
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('is_image = 0');

        return $this->fetchAll($selection);
    }

    public function findByContentType($contentType, $asPairs = false)
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('content_type LIKE ?', $contentType);

        return $this->fetchAll($selection);
    }

    public function findByNamespace($namespace)
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->select(self::MEDIA_STORAGE_TABLE.'.*')
            ->select(':'.self::MEDIA_USAGE_TABLE.'.namespace')
            ->select(':'.self::MEDIA_USAGE_TABLE.'.primary')
            ->where(':'.self::MEDIA_USAGE_TABLE.'.namespace LIKE ?', $namespace);

        $this->applyAdditionalConditions($selection);

        return $this->fetchAll($selection);
    }

    public function findByCustomWhere($custom)
    {
        $params = array_merge([$custom['where']], $custom['params']);
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE);;

        call_user_func_array([$selection, 'where'], $params);

        return $this->fetchAll($selection);
    }

    public function getByNamespace($namespace)
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->select(self::MEDIA_STORAGE_TABLE.'.*')
            ->select(':'.self::MEDIA_USAGE_TABLE.'.namespace')
            ->select(':'.self::MEDIA_USAGE_TABLE.'.primary')
            ->where(':'.self::MEDIA_USAGE_TABLE.'.namespace LIKE ?', $namespace);

        $this->applyAdditionalConditions($selection);

        return $this->fetch($selection);
    }

    public function getById($id)
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('id = ?', $id);

        $this->applyAdditionalConditions($selection);

        return $this->fetch($selection);
    }

    public function get($name, $namespace)
    {
        $selection = $this->context->table(self::MEDIA_STORAGE_TABLE)
            ->where('name LIKE ?', $name);
        if (!is_null($namespace)) {
            $selection->select(self::MEDIA_STORAGE_TABLE.'.*')
                ->select(':'.self::MEDIA_USAGE_TABLE.'.namespace')
                ->select(':'.self::MEDIA_USAGE_TABLE.'.primary')
                ->where(':'.self::MEDIA_USAGE_TABLE.'.namespace LIKE ?', $namespace);
        }
        return $this->fetch($selection);
    }

    private function fetch(Selection $selection) {
        if ((bool)$this->as_pairs) {
            return $selection->fetchPairs($this->pairs[0], $this->pairs[1]);
        } else {
            return $selection->fetch();
        }
    }

    private function fetchAll(Selection $selection) {
        if ((bool)$this->as_pairs) {
            return $selection->fetchPairs($this->pairs[0], $this->pairs[1]);
        } else {
            return $selection->fetchAll();
        }
    }

    public function saveUsage($data)
    {
        if (isset($data['id'])) {
            return $this->context->table(self::MEDIA_USAGE_TABLE)->update($data);
        } else {
            $usage = $this->context->table(self::MEDIA_USAGE_TABLE)
                ->where('namespace LIKE ?', $data['namespace'])
                ->where('media_id = ?', $data['media_id'])
                ->fetch();
            /** If we have this usage, we don't need insert new. */
            if ($usage) {
                return $usage;
            }

            $data['id'] = uniqid('', true);
            return $this->context->table(self::MEDIA_USAGE_TABLE)->insert($data);
        }
    }

    public function removeUsage($data)
    {
        if (isset($data['id'])) {
            return $this->context->table(self::MEDIA_USAGE_TABLE)->where('id = ?', $data['id'])->delete();
        } else {
            return $this->context->table(self::MEDIA_USAGE_TABLE)
                ->where('namespace LIKE ? ', $data['namespace'])
                ->where('media_id = ?', $data['media_id'])
                ->delete();
        }
    }

    /**
     * @param string $namepsace
     * @param string|bool $mediaId if string set primary for concrete media_id, if false, unset primary for whole namespace
     * @return mixed
     */
    public function setPrimary($namespace, $mediaId)
    {
        $selection = $this->context->table(self::MEDIA_USAGE_TABLE)
            ->where('namespace LIKE ?', $namespace);
        if ($mediaId === false) {
            return $selection->update(['primary' => false]);
        } else {
            $selection->where('media_id = ?', $mediaId)->update(['primary' => true]);
        }
    }

    public function deleteFiles(IFileFilter $filter)
    {
        $files = $this->find($filter);

        $ids = array_keys($files);

        return $this->context->table(self::MEDIA_STORAGE_TABLE)->where('id IN ?', $ids)->delete();
    }

    public function deleteById($id)
    {
        return $this->context->table(self::MEDIA_STORAGE_TABLE)->where('id = ?', $id)->delete();
    }

    public function deleteUsageByNamespace($ns)
    {
        return $this->context->table(self::MEDIA_USAGE_TABLE)->where('namespace LIKE ?', $ns)->delete();
    }

    private function applyAdditionalConditions(Selection $selection)
    {
        $additionalParameters = $this->currentFilter->getAdditionalConditions();
        if (array_key_exists(self::MEDIA_STORAGE_TABLE, $additionalParameters)) {
            foreach ($additionalParameters[self::MEDIA_STORAGE_TABLE] as $condition) {
                $selection->where($condition);
            }
        }
        if (array_key_exists(self::MEDIA_USAGE_TABLE, $additionalParameters)) {
            foreach ($additionalParameters[self::MEDIA_USAGE_TABLE] as $condition) {
                $selection->where(':'.self::MEDIA_USAGE_TABLE.'.'.$condition);
            }
        }
    }
}