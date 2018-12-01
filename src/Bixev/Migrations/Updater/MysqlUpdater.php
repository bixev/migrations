<?php

namespace Bixev\Migrations\Updater;

class MysqlUpdater extends AbstractUpdater
{

    protected $_replacements = [];

    /**
     * @var callable
     */
    protected $_queryExecutor;

    /**
     * @param array $replacements array of replacements to replace in query strings
     */
    public function setReplacements(array $replacements = [])
    {
        $this->_replacements = $replacements;
    }

    public function setQueryExecutor(callable $callable)
    {
        $this->_queryExecutor = $callable;
    }

    protected function doUpdate($path)
    {
        $content = file_get_contents($path);
        $queries = explode(";\n", $content);
        foreach ($queries as $query) {
            if (trim($query) != '') {
                if ($this->_queryExecutor === null) {
                    throw new \Exception('query executor is not defined');
                }
                call_user_func($this->_queryExecutor, $query);
            }
        }
    }

}
