<?php

require_once 'Skit/Tigra/Model/Migration/Abstract.php';

class Skit_Tigra_Model_Db extends Varien_Object {
    protected $_log = array();

    protected function _log($line) {
        // append to the log if runed in shell
        if (!$this->getIsCli())
            $this->_log[] = $line;
        else
            echo $line . "\n";
    }

    public function getLog() {
        return $this->_log;
    }

    /**
     * Wrap migrations into transaction and apply them
     *
     * @param bool determines if migrations must be reverted
     */
    public function applyMigrations($forward = true, $to = false, $force = false) {
        $direction = $forward ? 'up' : 'down';

        $migrationModel = Mage::getModel('tigra/migration');
        $migrations = Mage::getModel('tigra/migration_file_collection');

        $migrations = $forward ? $migrations->addNotAppliedFilter($to) :
                        $migrations->addAppliedFilter($to ?: $this->getLastApplied());

        if (count($migrations)) {
            // check if there are missing migrations
            if (!$force && $migrations->hasMissingItems($forward, (int)$to)) {
                if ($this->getIsCli()) {
                    $pass = readline('It appears that we miss some migrations, ' .
                     'do you want to continue anyway? (y/n) ');

                    if (strtolower($pass) !== 'y') {
                        $this->_log('Aborting.');
                        return false;
                    }
                } else {
                    $msg = 'Missing migrations. Aborting.';
                    $this->_log($msg);
                    throw new Exception($msg);
                }
            }

            // initialize transaction
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $date = Mage::getSingleton('core/date');

            foreach ($migrations as $migration) {
                $this->_log('Loading ' . $migration->getBasename());

                try {
                    $write->beginTransaction();

                    $start = $date->gmtDate();
                    $migText = file_get_contents($migration->getFilename());
                    $migData = explode('_', basename($migration->getBasename()));

                    // get the migration name
                    preg_match('/class\s+(\w+)\s+[\w\s]*?extends\s+Tigra_Migration/', $migText, $className);

                    include($migration->getFilename());

                    $migrationObj = new $className[1];
                    $migrationObj->$direction();

                    if ($forward) {
                        // add new migrations to the DB changelog
                        $migrationModel->setChangeNumber($migration->getNum())
                            ->setStartDt($start)
                            ->setCompleteDt($date->gmtDate())
                            ->setDescription($className[1])
                            ->save();
                    } else {
                        // remove rolled transactions
                        $migrationModel->load($migration->getNum())
                            ->delete();
                    }

                    $write->commit();
                } catch (Exception $e) {
                    // undo all changes
                    $write->rollback();

                    $this->_log($e->getMessage());
                    $this->_log(sprintf("DB migration (%s) FAILED.\n\nRolling back.", $migration->getBasename()));
                    return false;
                }
            }

            $this->_log('SUCCESS: DB has been ' . ($forward ? 'upgraded' : 'downgraded' ));
        } else {
            $this->_log("There's no migrations to apply.");
            return false;
        }

        return true;
    }

    /**
     * Generated the skeleton migration
     */
    public function generate($name) {
        // prepare migration name to use in class name
        $name = preg_replace('/[^\w\s]/', '', $name);
        $name = preg_replace('/_+/', ' ', $name);
        $name = preg_replace('/\s+/', '_', ucwords(trim($name)));

        $migrations = Mage::getModel('tigra/migration_file_collection');

        if (!$migrations->hasItem($name)) {
            $template = <<<EOF
<?php

class {name} extends Tigra_Migration {
    public function up() {
        // place your upgrading code here
    }

    public function down() {
        // place your downgrade code here
    }
}

EOF;

            $migration = str_replace('{name}', $name, $template);

            $num = $this->getNextAvailableNum();
            $name = $num . '_' . strtolower($name) . '.php';

            $path = Mage::getStoreConfig('dev/tigra/path') ?:
                        Mage::getBaseDir() . DS . 'migrations';

            $path .= DS . $name;

            file_put_contents($path, $migration);

            $this->_log('The migration (' . $name . ') has been successfully created.');
        } else {
            $this->_log("ABORT: Another migration is already named \"$name\"");
        }
    }

    public function formatLastApplied() {
        $this->_log('The DB is at the state of ' . sprintf('%03d', $this->getLastApplied()));
    }

    /**
     * Last applied migration number
     * @return integer
     */
    public function getLastApplied() {
        return Mage::getResourceModel('tigra/migration')->getLast();
    }

    /**
     * Migration number generator
     * @return integer
     */
    public function getNextAvailableNum() {
        $collection = Mage::getModel('tigra/migration_file_collection')
            ->setOrder('basename', 'DESC');

        if (count($collection)) {
            $last = current($collection->getItems());
            $last = (int)$last->getNum();
        } else {
            $last = 0;
        }

        return sprintf('%03d', $last + 1);
    }
}
