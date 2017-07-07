<?php
/**
 * Model工厂模式
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Models;

use Exception;

class Factory
{
    /**
     * @var Factory
     */
    private static $instance;
    public $pool = [];

    /**
     * ModelFactory constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * 获取单例
     * @return Factory
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            new Factory();
        }
        return self::$instance;
    }

    /**
     * 获取一个model
     * @param $model
     * @return mixed
     * @throws Exception
     */
    public function getModel($model)
    {
        $className = $model;
        do {
            if (class_exists($className)) {
                break;
            }

            $className = "\\App\\Models\\$model";
            if (class_exists($className)) {
                break;
            }

            $className = "\\PG\\MSF\\Models\\$model";
            if (class_exists($className)) {
                break;
            }

            throw new Exception("class $model is not exist");
        } while (0);

        $models = $this->pool[$className] ?? null;
        if ($models == null) {
            $models = $this->pool[$className] = new \SplStack();
        }

        if (!$models->isEmpty()) {
            $modelInstance = $models->shift();
            $modelInstance->isUse();
            $modelInstance->useCount++;
            return $modelInstance;
        }

        $modelInstance = new $className;
        $modelInstance->coreName = $className;
        $modelInstance->genTime  = time();
        $modelInstance->useCount = 1;

        return $modelInstance;
    }

    /**
     * 归还一个model
     * @param $model Model
     */
    public function revertModel(&$model)
    {
        if (!$model->getIsDestroy()) {
            $model->destroy();
        }

        $this->pool[$model->coreName]->push($model);
    }
}