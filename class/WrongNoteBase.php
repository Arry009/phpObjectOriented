<?php
/**
 * Created by PhpStorm.
 * User: King
 * Date: 2018/4/21
 * Time: 16:01
 * Email: jackying009@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

abstract class WrongNoteBase implements  WrongNoteInterface
{
    public function statistics()
    {
        // TODO: Implement statistics() method.
        $select = '';//$this->select();
        $where = '';//$this->>where();
        return $this->find()->select($select)->where($where)->asArray()->all();
    }

}