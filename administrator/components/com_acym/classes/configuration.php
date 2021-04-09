<?php

namespace AcyMailing\Classes;

use AcyMailing\Libraries\acymClass;

class ConfigurationClass extends acymClass
{
    var $table = 'configuration';
    var $pkey = 'name';
    var $values = [];

    public function load()
    {
        $this->values = acym_loadObjectList('SELECT * FROM #__acym_configuration', 'name');
    }

    public function get($namekey, $default = '')
    {
        if (isset($this->values[$namekey])) {
            return $this->values[$namekey]->value;
        }

        return $default;
    }

    public function save($newConfig)
    {
        $query = 'REPLACE INTO #__acym_configuration (`name`, `value`) VALUES ';

        $params = [];
        foreach ($newConfig as $name => $value) {
            if (strpos($name, 'password') !== false && !empty($value) && trim($value, '*') == '') {
                continue;
            }

            if ($name === 'multilingual' && $value === '1') {
                $remindme = json_decode($this->get('remindme', '[]'), true);
                if (!in_array('multilingual', $remindme)) {
                    $remindme[] = 'multilingual';
                    $this->save(['remindme' => json_encode($remindme)]);
                }
            }

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            if (empty($this->values[$name])) {
                $this->values[$name] = new \stdClass();
            }
            $this->values[$name]->value = $value;

            $params[] = '('.acym_escapeDB(strip_tags($name)).','.acym_escapeDB(strip_tags($value)).')';
        }

        if (empty($params)) return true;

        $query .= implode(',', $params);

        try {
            $status = acym_query($query);
        } catch (\Exception $e) {
            $status = false;
        }
        if ($status === false) {
            acym_display(isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200).'...', 'error');
        }

        return $status;
    }

    public function setLicenseKeyByDomain()
    {
        if (!acym_level(1)) return true;
    }
}
