<?php

namespace UIS\Core\Auth;

class AuthTokenProvider implements AuthTokenProviderContract
{
    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * @return void
     */
    public function __construct($model = 'UIS\Core\Auth\AuthToken')
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    public function create(array $data)
    {
        $model = $this->createModel();
        $model->fill($data)->save();

        return $model;
    }

    public function delete($id)
    {
        $model = $this->createModel();
        $model->newQuery()->where($model->getKeyName(), $id)->delete();
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }
}
