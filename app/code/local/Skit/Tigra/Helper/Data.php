<?php

class Skit_Tigra_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * Get migration information based on the filename
     *
     * @param string
     * @return array
     */
    public function getInfoByName($filename) {
        $info = explode('_', basename($filename, '.php'), 2);

        return array(
                    'num' => $info[0],
                    'description' => $info[1]
                );
    }

    /**
     * Returns the number of given migration
     *
     * @param string Migration filename
     * @return string (001, 002 etc)
     */
    public function getNumByName($name) {
        $info = $this->getInfoByName($name);
        return $info['num'];
    }
}
