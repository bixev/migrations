<?php
namespace Bixev\Migrations\Updater;

class MysqlUpdater extends AbstractUpdater
{
    protected function doUpdate($path)
    {
        $content = file_get_contents($path);
        $queries = explode(";\n", $content);
        foreach ($queries as $query) {
            if (trim($query) != '') {
                db()->setLongQueriesLog(false);
                db()->query($query);
            }
        }
    }

}
