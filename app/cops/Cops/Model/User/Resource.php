<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cops\Model\User;

use Cops\Model\ResourceAbstract;
use PDO;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;

/**
 * User resource model
 * @author Mathieu Duplouy <mathieu.duplouy@gmail.com>
 */
class Resource extends ResourceAbstract
{
    /**
     * Resource table name
     */
    const TABLE_NAME = 'user';

    /**
     * Get the DB connection instance
     * Overloaded to use internal connection
     *
     * @return Doctrine\DBAL\Connection
     */
    protected function getConnection()
    {
        return $this->app['dbs']['silexCops'];
    }

    /**
     * Insert user data
     */
    public function insert()
    {
        $con = $this->getConnection();
        $con->insert(self::TABLE_NAME,
            array(
                'username' => $this->getEntity()->getUsername(),
                'password' => $this->app['security.encoder.digest']->encodePassword(
                    $this->getEntity()->getPassword(),
                    ''
                ),
                'roles'     => $this->getEntity()->getRole(),
            ),
            array(
                PDO::PARAM_STR,
                PDO::PARAM_STR,
                PDO::PARAM_STR,
            )
        );
        return $con->lastInsertId();
    }

    /**
     * Load by username
     *
     * @param  string $username
     *
     * @return false|array
     */
    public function loadByUsername($username)
    {
        return $this->getQueryBuilder()
            ->select('main.*')
            ->from(self::TABLE_NAME, 'main')
            ->where('username = :username')
            ->setParameter('username', $username, PDO::PARAM_STR)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Load all users
     *
     * @return array
     */
    public function loadAll()
    {
        return $this->getQueryBuilder()
            ->select('main.*')
            ->from(self::TABLE_NAME, 'main')
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Initialize storage table
     *
     * @return void
     */
    public function initTable()
    {
        $this->dropTable()
            ->createTable()
            ->insertDefaultValues();
    }

    /**
     * Drop resource table
     *
     * @return $this
     */
    private function dropTable()
    {
        $schema = $this->getConnection()->getSchemaManager();
        if ($schema->tablesExist(self::TABLE_NAME)) {
            $schema->dropTable(self::TABLE_NAME);
        }

        return $this;
    }

    /**
     * Create resource table
     *
     * @return $this
     */
    private function createTable()
    {
        $schema = $this->getConnection()->getSchemaManager();

        $table = new Table(self::TABLE_NAME);
        $table->addColumn('id',       'integer', array('autoincrement' => true, 'unsigned' => true));
        $table->addColumn('username', 'string',  array('length' => 32));
        $table->addColumn('password', 'string',  array('length' => 100));
        $table->addColumn('roles',    'string',  array('length' => 20));
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('username'), 'uniq_username');

        $schema->createTable($table);

        return $this;
    }

    /**
     * Insert default value into table
     *
     * @return $this
     */
    private function insertDefaultValues()
    {
        $encryptedPassword = $this->app['security.encoder.digest']->encodePassword(
            $this->app['config']->getValue('default_password'),
            ''
        );

        $con = $this->getConnection();
        $con->insert(self::TABLE_NAME,
            array(
                'username' => $this->app['config']->getValue('default_login'),
                'password' => $encryptedPassword,
                'roles'    => $this->getEntity()->getAdminRole(),
            ),
            array(
                PDO::PARAM_STR,
                PDO::PARAM_STR,
                PDO::PARAM_STR,
            )
        );

        return $this;
    }
}