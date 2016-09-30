<?php

namespace ArcheeNic\HackBrainTime;

/**
 * Class secure
 * @RU Отлов возможных атак
 * @EN Catches and logs info about POST/GET/FILES in different log-files
 */
class Secure
{
    /**
     * @RU тип атаки (определяется через метод setAttackType)
     * @EN Attack Type
     * @see Secure::setAttackType()
     *
     * @var string
     */
    protected $attackType='unidentified';
    /**
     * @RU Конечная часть названия файла
     * @EN Extension of the log file name
     *
     * @var string
     */
    protected $postfix='.log';
    /**
     * @RU Абсолютный путь до места сохранения логов
     * @EN Absolute (or not) path for log storage
     *
     * @TODO Not only absolute path can be used here
     * @var string
     */
    protected $absPath='';
    /**
     * @RU Типы отлавливаемых входящих данных get,post,file
     * @EN Types of data, that is caught and logged
     *
     * @var string
     */
    protected $dataTypes='';
    /**
     *
     * @RU Class constructor
     * @EN Конструктор класса
     *
     * @param string $dataTypes Types of incoming data that are caught
     * @param string $absPath Absolute path for log storage
     * @param string $postfix Extension of log file
     */
    public function __construct($dataTypes = 'get,post,files', $absPath = '', $postfix = '.log')
    {
        $this->setVars($dataTypes, $absPath, $postfix);
        $this->fireTrigger();
        return $this; // make it chainable
    }

    /**  @region Simple setters /
     *           Простые операции по назначению атрибутов классов
     */

    /**
     * @RU Назначение абсолютного пути до места сохранения логов
     * @EN $absPath setter
     *
     * @param $absPath
     * @return Secure
     */
    protected function setPath($absPath)
    {
        if (!$absPath) {
            $absPath=__DIR__;
        }
        $absPath=trim($absPath);
        $absPath=rtrim($absPath, '/');
        $this->absPath=$absPath;
        return $this; // make it chainable
    }

    /**
     * @RU Назначение атрибутов классов
     * @EN Set all object properties
     *
     * @param $dataTypes
     * @param $absPath
     * @param $postfix
     */
    protected function setVars($dataTypes, $absPath, $postfix)
    {
        $this->setPath($absPath);
        $this->setAttackType();
        if (!$dataTypes) {
            // TODO: Лучше потом переделать в исключение
            print 'undefined var $types';
            exit;
        }
        $this->dataTypes=explode(',', $dataTypes);
        if (!$dataTypes) {
            // TODO: Лучше потом переделать в исключение
            print 'undefined var $dataTypes';
            exit;
        }
        $this->postfix=$postfix;
    }
    /**  @endregion */

    /**  @region Resolve possible attack type / Определение типа атаки */

    /**
     * @RU Есть ли попытка пролезть через админку
     * @EN User tries to use admin-part of WP
     *
     * @return bool
     */
    protected function attackTypeWpadmin()
    {
        $request=$_SERVER['REQUEST_URI'];
        return (strpos('wp_admin', $request)!==false);
    }

    /**
     * @RU Есть ли попытка пролезть через главную страницу
     * @EN User tries to use index.php of WP
     *
     * @return bool
     */
    protected function attackTypeIndex()
    {
        $request=$_SERVER['REQUEST_URI'];
        return (strpos('index.php', $request)!==false);
    }

    /**
     * @RU Назначение типа
     * @EN Defines attack type
     */
    protected function setAttackType()
    {
        if ($this->attackTypeWpadmin()) {
            $this->attackType='wpadmin';
        } elseif ($this->attackTypeIndex()) {
            $this->attackType='index';
        }
        return $this; // Make it chainable
    }
    /**  @endregion */

    /**  @region File operation and log contents methods
     *           Работа с файлом и содержимым лога
     */

    /**
     * @RU Формирование строки лога
     * @EN Create a line of log
     *
     * @param string $separator Divider used to separate data fields
     * @return string
     */
    protected function logString($separator = "\t")
    {
        $data=array();
        $data[]=time();
        $data[]='POST';
        $data[]=json_encode($_POST);
        $data[]='GET';
        $data[]=json_encode($_GET);
        $data[]='PHPSELF';
        $data[]=$_SERVER ['PHP_SELF'];
        return implode($separator, $data);
    }

    /**
     * @RU Сохранение лога
     * @EN Save generated line to log
     *
     * @return Secure
     */
    protected function saveLog()
    {
        $file=$this->absPath.'/'.$this->attackType.$this->postfix;
        file_put_contents($file, $this->logString()."\r\n", FILE_APPEND);
        return $this;
    }
    /** endregion */

    /**
     * @RU Вызвать событие по типу входящих данных
     * @EN Trigger action depending on datatype
     *
     * @return Secure
     */
    protected function fireTrigger()
    {
        // TODO: Eliminate repeats in code
        if (in_array('get', $this->dataTypes)&&!empty($_GET)) {
            $this->saveLog();
        } elseif (in_array('files', $this->dataTypes)&&!empty($_FILES)) {
            $this->saveLog();
        } elseif (in_array('post', $this->dataTypes)&&!empty($_POST)) {
            $this->saveLog();
        }

        return $this;
    }
}
